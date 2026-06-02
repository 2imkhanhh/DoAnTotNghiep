import { defineStore } from 'pinia';
import { useAuthStore } from './auth';
import axios from 'axios';

export const useChatStore = defineStore('chat', {
    state: () => ({
        conversations: [],
        activeConversation: null,
        unreadMessagesCount: 0,
        loading: false,
    }),

    actions: {
        /**
         * Tải toàn bộ danh sách cuộc trò chuyện.
         */
        async fetchConversations() {
            this.loading = true;
            try {
                const response = await axios.get('/api/conversations');
                if (response.data.success) {
                    this.conversations = response.data.data;
                    
                    // Tính tổng số tin nhắn chưa đọc toàn cục
                    this.unreadMessagesCount = this.conversations.reduce((sum, item) => {
                        return sum + (item.unread_messages_count || 0);
                    }, 0);

                    // Thiết lập lắng nghe sự kiện real-time toàn cục trên các cuộc trò chuyện này
                    this.listenForNewMessagesGlobal();
                }
            } catch (error) {
                console.error('Lỗi khi tải danh sách hội thoại:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Thiết lập cuộc hội thoại đang mở.
         */
        async setActiveConversation(conversation) {
            this.activeConversation = conversation;
            if (conversation) {
                if (conversation.unread_messages_count > 0) {
                    await this.markAsRead(conversation.id);
                }
            }
        },

        /**
         * Đánh dấu cuộc hội thoại là đã đọc.
         */
        async markAsRead(conversationId) {
            try {
                await axios.post(`/api/conversations/${conversationId}/read`);
                
                // Cập nhật lại state cục bộ để giao diện phản hồi lập tức
                const conversation = this.conversations.find(c => Number(c.id) === Number(conversationId));
                if (conversation) {
                    // Giảm tổng số chưa đọc toàn cục
                    this.unreadMessagesCount = Math.max(0, this.unreadMessagesCount - conversation.unread_messages_count);
                    // Đặt chưa đọc của cuộc này về 0
                    conversation.unread_messages_count = 0;
                }
            } catch (error) {
                console.error('Lỗi khi đánh dấu đã đọc:', error);
            }
        },

        /**
         * Lắng nghe WebSockets cho tất cả các cuộc hội thoại hiện có.
         */
        listenForNewMessagesGlobal() {
            if (!window.Echo) {
                // Thử khởi tạo lại Echo nếu chưa có
                if (typeof window.initializeEcho === 'function') {
                    window.initializeEcho();
                }
                if (!window.Echo) return;
            }

            this.conversations.forEach(conversation => {
                const channelName = `chat.${conversation.id}`;
                
                // Ngắt lắng nghe cũ trước khi đăng ký mới để tránh trùng lặp
                window.Echo.leave(channelName);

                window.Echo.private(channelName)
                    .listen('.message.sent', (e) => {
                        this.handleIncomingMessage(e.message);
                    })
                    .listen('.message.deleted', (e) => {
                        this.handleDeletedMessage(e);
                    });
            });

            // Lắng nghe channel cá nhân của user để nhận tin nhắn từ các cuộc trò chuyện MỚI
            const authStore = useAuthStore();
            if (authStore.user && authStore.user.id) {
                const userChannelName = `App.Models.User.${authStore.user.id}`;
                window.Echo.leave(userChannelName);
                window.Echo.private(userChannelName)
                    .listen('.message.sent', (e) => {
                        // Nếu tin nhắn thuộc về cuộc trò chuyện chưa có trong danh sách
                        const exists = this.conversations.find(c => Number(c.id) === Number(e.message.conversation_id));
                        if (!exists) {
                            this.fetchConversations();
                        } else {
                            this.handleIncomingMessage(e.message);
                        }
                    })
                    .listen('.message.deleted', (e) => {
                        this.handleDeletedMessage(e);
                    });
            }
        },

        /**
         * Xử lý tin nhắn real-time được gửi đến.
         */
        handleIncomingMessage(message) {
            const conversation = this.conversations.find(c => Number(c.id) === Number(message.conversation_id));
            
            if (conversation) {
                // Kiểm tra xem sự kiện tin nhắn này đã được xử lý chưa để tránh tính trùng lặp số đếm
                if (conversation.latest_message && Number(conversation.latest_message.id) === Number(message.id)) {
                    return;
                }

                // Chuẩn hóa dữ liệu Widget đính kèm trong tin nhắn (Hybrid model)
                if (message.post) {
                    const post = message.post;
                    post.image = post.images && post.images.length > 0 ? post.images[0].image_path : null;
                    post.orders = post.orders || [];
                    
                    // Cập nhật hình ảnh thu nhỏ cho cuộc trò chuyện ở Sidebar
                    conversation.post = {
                        id: post.id,
                        title: post.title,
                        slug: post.slug,
                        price: post.price,
                        image: post.image
                    };
                }

                // Cập nhật tin nhắn mới nhất
                conversation.latest_message = {
                    id: message.id,
                    message_text: message.message_text,
                    image_path: message.image_path,
                    sender_id: message.sender_id,
                    is_read: message.is_read,
                    created_at: message.created_at
                };

                // Đẩy cuộc trò chuyện này lên đầu danh sách hội thoại
                this.conversations = [
                    conversation,
                    ...this.conversations.filter(c => Number(c.id) !== Number(conversation.id))
                ];

                // Nếu đây chính là cuộc hội thoại đang mở
                if (this.activeConversation && Number(this.activeConversation.id) === Number(message.conversation_id)) {
                    // Đánh dấu đã đọc ngay lập tức
                    this.markAsRead(conversation.id);
                    
                    // Phát đi sự kiện toàn cục để giao diện Chat.vue biết và nạp tin nhắn mới
                    const event = new CustomEvent('new-message-received', { detail: message });
                    window.dispatchEvent(event);
                } else {
                    // Nếu là cuộc hội thoại khác, tăng số tin nhắn chưa đọc
                    conversation.unread_messages_count++;
                    this.unreadMessagesCount++;
                }
            } else {
                // Nếu đây là một cuộc hội thoại hoàn toàn mới chưa có trong danh sách (người khác bắt đầu chat với mình)
                // Tiến hành tải lại danh sách để cập nhật
                this.fetchConversations();
            }
        },

        /**
         * Xử lý tin nhắn bị thu hồi real-time.
         */
        handleDeletedMessage(eventData) {
            const { messageId, conversationId } = eventData;
            
            // Phát sự kiện tới giao diện Chat.vue để xóa bong bóng tin nhắn
            const event = new CustomEvent('message-deleted-received', { detail: messageId });
            window.dispatchEvent(event);
            
            // Nếu tin nhắn vừa bị xóa là latest_message, gọi fetchConversations để làm mới danh sách ngoài sidebar
            const conversation = this.conversations.find(c => Number(c.id) === Number(conversationId));
            if (conversation && conversation.latest_message && Number(conversation.latest_message.id) === Number(messageId)) {
                this.fetchConversations();
            }
        },

    }
});
