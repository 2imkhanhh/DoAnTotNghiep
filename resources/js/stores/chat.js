import { defineStore } from 'pinia';
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
            if (conversation && conversation.unread_messages_count > 0) {
                await this.markAsRead(conversation.id);
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
                    });
            });
        },

        /**
         * Xử lý tin nhắn real-time được gửi đến.
         */
        handleIncomingMessage(message) {
            const conversation = this.conversations.find(c => Number(c.id) === Number(message.conversation_id));
            
            if (conversation) {
                // Đồng bộ thông tin bài viết mới nếu có thay đổi từ phía đối phương gửi đính kèm
                if (message.conversation && message.conversation.post) {
                    const post = message.conversation.post;
                    const primaryImage = post.images && post.images.length > 0 ? post.images[0].image_path : null;
                    const formattedPost = {
                        id: post.id,
                        title: post.title,
                        slug: post.slug,
                        price: post.price,
                        status: post.status,
                        image: primaryImage
                    };
                    
                    conversation.post = formattedPost;
                    if (this.activeConversation && Number(this.activeConversation.id) === Number(conversation.id)) {
                        this.activeConversation.post = formattedPost;
                    }
                }

                // Cập nhật tin nhắn mới nhất
                conversation.latest_message = {
                    id: message.id,
                    message_text: message.message_text,
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
        }
    }
});
