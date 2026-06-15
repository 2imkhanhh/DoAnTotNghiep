<template>
  <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end font-sans">

    <!-- Chat Window -->
    <div v-if="isOpen"
      class="bg-surface-container-lowest w-[300px] sm:w-[340px] h-[480px] max-h-[80vh] rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.2)] flex flex-col overflow-hidden border border-outline-variant transition-all duration-300 transform origin-bottom-right">

      <!-- Header -->
      <div class="bg-primary text-on-primary p-3 flex justify-between items-center shadow-md z-10">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center shrink-0 shadow-sm">
            <span class="material-symbols-outlined text-primary text-xl font-bold">smart_toy</span>
          </div>
          <div>
            <h3 class="font-bold text-[15px] leading-tight tracking-wide">Trợ lý AI</h3>
            <span class="text-[11px] opacity-90 flex items-center gap-1 font-medium">
              <span class="w-1.5 h-1.5 rounded-full bg-green-400 shadow-[0_0_5px_#4ade80]"></span> Trực tuyến
            </span>
          </div>
        </div>
        <div class="flex items-center gap-1">
          <button @click="resetChat" title="Làm mới"
            class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">sync</span>
          </button>
          <button @click="isOpen = false" title="Thu nhỏ"
            class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-[20px]">remove</span>
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div ref="messagesContainer" class="flex-1 p-3 overflow-y-auto flex flex-col bg-white scroll-smooth">

        <!-- Vòng lặp hiển thị tin nhắn -->
        <div v-for="(msg, index) in messages" :key="index"
          :class="['flex w-full gap-2 mb-3', msg.role === 'user' ? 'justify-end' : 'justify-start']">

          <!-- Bot Avatar -->
          <div v-if="msg.role === 'model'" class="w-7 h-7 shrink-0 flex items-end">
            <div v-if="!msg.hideAvatar" class="w-7 h-7 bg-primary rounded-full flex items-center justify-center">
              <span class="material-symbols-outlined text-white text-[15px]">smart_toy</span>
            </div>
          </div>

          <!-- Bong bóng tin nhắn -->
          <div
            :class="['max-w-[75%] rounded-2xl p-2.5 shadow-sm',
              msg.role === 'user' ? 'bg-primary text-on-primary rounded-br-sm' : 'bg-[#f1f5f9] text-gray-800 rounded-bl-sm']">

            <!-- Nội dung text -->
            <div class="whitespace-pre-wrap leading-relaxed text-[13px]" v-html="formatMessage(msg.content)"></div>

            <!-- Hiển thị Thẻ Sản phẩm (Mini Card) nếu Bot trả về mảng Data -->
            <div v-if="msg.data && msg.data.length > 0"
              class="mt-3 flex overflow-x-auto gap-2 pb-2 -mx-1 px-1 snap-x product-scrollbar">
              <router-link v-for="product in msg.data" :key="product.id" :to="`/post/${product.slug}`"
                class="min-w-[140px] w-[140px] bg-white rounded-xl overflow-hidden border border-outline-variant shadow-sm shrink-0 snap-start block hover:border-primary hover:shadow-md transition-all group">

                <!-- Ảnh sản phẩm -->
                <div class="w-full h-20 bg-gray-100 flex items-center justify-center overflow-hidden">
                  <img :src="getPrimaryImage(product)"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    alt="Product Image" />
                </div>

                <div class="p-2 flex flex-col gap-1">
                  <span
                    class="font-bold text-on-surface text-[11px] line-clamp-2 leading-snug h-7 group-hover:text-primary transition-colors">{{
                      product.title }}</span>
                  <span class="text-error font-bold text-[12px] mt-0.5">{{ formatPrice(product.price) }}đ</span>
                  <div class="mt-0.5 text-[9px] text-on-surface-variant flex items-center gap-0.5 line-clamp-1">
                    <span class="material-symbols-outlined text-[10px]">location_on</span>
                    {{ product.ward_name ? product.ward_name + ', ' : '' }}{{ product.province_name }}
                  </div>
                </div>
              </router-link>
            </div>

          </div>
        </div>

        <!-- Hiệu ứng "Đang gõ..." (Loading) -->
        <div v-if="isLoading" class="flex justify-start w-full gap-2 mb-3">
          <div class="w-7 h-7 shrink-0 flex items-end">
            <div class="w-7 h-7 bg-primary rounded-full flex items-center justify-center">
              <span class="material-symbols-outlined text-white text-[15px]">smart_toy</span>
            </div>
          </div>
          <div class="bg-[#f1f5f9] rounded-2xl rounded-bl-sm px-3 py-2.5 shadow-sm flex items-center gap-1.5 h-[34px]">
            <div class="w-1.5 h-1.5 rounded-full bg-gray-500 animate-bounce" style="animation-delay: 0s"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-gray-500 animate-bounce" style="animation-delay: 0.15s"></div>
            <div class="w-1.5 h-1.5 rounded-full bg-gray-500 animate-bounce" style="animation-delay: 0.3s"></div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-2.5 bg-white border-t border-outline-variant z-10 flex flex-col gap-1.5">
        <form @submit.prevent="sendMessage" class="flex items-center gap-2 relative">
          <input v-model="newMessage" type="text" placeholder="Nhập câu hỏi..."
            class="flex-1 bg-surface-container-lowest border border-outline-variant rounded-full py-2 pl-4 pr-10 text-[13px] focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-inner transition-shadow"
            :disabled="isLoading"
            @focus="scrollToBottom" />
          <button type="submit" :disabled="!newMessage.trim() || isLoading"
            class="absolute right-1 w-8 h-8 flex items-center justify-center rounded-full bg-primary text-on-primary disabled:opacity-50 disabled:bg-gray-300 hover:bg-primary-container hover:text-on-primary-container transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-[16px]">send</span>
          </button>
        </form>
        <div class="text-left text-[10px] text-gray-500 font-medium">
          Thông tin chỉ mang tính tham khảo, được tư vấn bởi Trí Tuệ Nhân Tạo.
        </div>
      </div>
    </div>

    <!-- Bong bóng Chat tự động (Speech Bubble) & Nút nổi -->
    <div v-if="!isOpen" class="relative flex flex-col items-end">

      <!-- Speech Bubble (Cố định khung, chỉ mờ text) -->
      <transition name="fade">
        <div v-if="showSpeechBubble"
          class="absolute bottom-[75px] right-0 w-[260px] bg-white rounded-2xl shadow-[0_8px_25px_rgba(0,0,0,0.12)] p-3 border border-gray-100 flex items-start gap-3 origin-bottom-right cursor-pointer hover:shadow-[0_8px_30px_rgba(0,0,0,0.18)] transition-shadow group"
          @click="isOpen = true; showSpeechBubble = false">

          <!-- Nút X để đóng (Hiện khi hover) -->
          <button @click.stop="showSpeechBubble = false"
            class="absolute top-1.5 right-1.5 w-5 h-5 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-all z-10 cursor-pointer">
            <span class="material-symbols-outlined text-[14px] font-bold">close</span>
          </button>

          <!-- Avatar Mini -->
          <div class="w-8 h-8 rounded-full bg-primary shrink-0 flex items-center justify-center shadow-sm">
            <span class="material-symbols-outlined text-white text-[16px] font-bold">smart_toy</span>
          </div>

          <!-- Khung chứa Text -->
          <div class="flex-1 mt-0.5 overflow-hidden pr-3">
            <h4 class="font-bold text-gray-800 text-[13px] mb-1">Chợ Đồ Cũ UTT</h4>
            <!-- Hiệu ứng thay đổi text -->
            <transition name="text-fade" mode="out-in">
              <p :key="currentBubbleIndex" class="text-[13px] text-gray-600 leading-snug">{{
                bubbleTexts[currentBubbleIndex] }}</p>
            </transition>
          </div>

          <!-- Mũi tên (Tail) -->
          <div
            class="absolute -bottom-2 right-6 w-4 h-4 bg-white border-b border-r border-gray-100 transform rotate-45">
          </div>
        </div>
      </transition>

      <!-- Nút nổi AI Icon (Đã đổi màu) -->
      <button @click="isOpen = true; showSpeechBubble = false"
        class="w-14 h-14 bg-primary text-on-primary rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-105 transition-all z-10 overflow-hidden relative group cursor-pointer mt-4">
        <span class="material-symbols-outlined text-3xl animate-pulse">smart_toy</span>
        <!-- Hiệu ứng sáng bóng hover -->
        <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
      </button>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const showSpeechBubble = ref(true);

// Khởi tạo 2 tin nhắn mặc định
const messages = ref([
  { role: 'model', content: 'Xin chào Anh/Chị! Em là trợ lý AI của Chợ Đồ Cũ UTT', hideAvatar: true },
  { role: 'model', content: 'Em rất sẵn lòng hỗ trợ Anh/Chị 😊', hideAvatar: false }
]);

const newMessage = ref('');
const isLoading = ref(false);
const messagesContainer = ref(null);

// Tự động lướt xuống cuối khi mở ô chatbot
watch(isOpen, (newVal) => {
  if (newVal) {
    scrollToBottom();
  }
});

// Danh sách các câu thoại sẽ tự động thay đổi ở Bubble
const bubbleTexts = [
  "Xin chào Anh/Chị! Em là trợ lý AI của Chợ Đồ Cũ UTT",
  "Em rất sẵn lòng hỗ trợ Anh/Chị 😊"
];
const currentBubbleIndex = ref(0);
let bubbleInterval = null;

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

// Hàm hỗ trợ in đậm text bằng markdown **text**
const formatMessage = (text) => {
  if (!text) return '';
  return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
};

const getPrimaryImage = (product) => {
  if (product.images && product.images.length > 0) {
    // Ưu tiên ảnh chính (is_primary = 1), nếu không có thì lấy ảnh đầu tiên
    const primary = product.images.find(img => img.is_primary === 1) || product.images[0];
    // Trong Database đã lưu sẵn chữ /storage/ rồi nên ta chỉ cần lấy nguyên xi đường dẫn
    return primary.image_path;
  }
  // Dùng ảnh SVG mặc định mã hóa sẵn (Không cần gọi mạng)
  return `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23cbd5e1"><rect width="100" height="100" fill="%23f1f5f9"/><path d="M50 20 L20 40 L20 70 L50 90 L80 70 L80 40 Z" fill="%23e2e8f0" stroke="%2394a3b8" stroke-width="2"/><path d="M50 20 L50 55 M20 40 L50 55 L80 40" stroke="%2394a3b8" stroke-width="2" fill="none"/></svg>`;
};

const resetChat = async () => {
  // Nếu chưa có tin nhắn nào của người dùng, tức là đoạn chat đã mới tinh rồi -> Không gửi request lên server nữa
  const hasUserMessage = messages.value.some(msg => msg.role === 'user');
  if (!hasUserMessage) return;

  // Tiến hành xoá luôn giao diện
  messages.value = [
    { role: 'model', content: 'Xin chào Anh/Chị! Em là trợ lý AI của Chợ Đồ Cũ UTT', hideAvatar: true },
    { role: 'model', content: 'Em rất sẵn lòng hỗ trợ Anh/Chị 😊', hideAvatar: false }
  ];

  try {
    const sessionId = localStorage.getItem('chatbot_session_id');
    const response = await axios.post('/api/chatbot/reset', { session_id: sessionId });
    if (response.data.status === 'success') {
      localStorage.setItem('chatbot_session_id', response.data.session_id);
    }
  } catch (error) {
    console.error('Lỗi khi reset chat:', error);
  }
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const sendMessage = async () => {
  const text = newMessage.value.trim();
  if (!text) return;

  // Đẩy tin nhắn của User vào giao diện
  messages.value.push({ role: 'user', content: text });
  newMessage.value = '';
  isLoading.value = true;
  scrollToBottom();

  try {
    const sessionId = localStorage.getItem('chatbot_session_id');
    const response = await axios.post('/api/chatbot/chat', {
      message: text,
      session_id: sessionId
    });

    if (response.data.status === 'success') {
      if (response.data.session_id && response.data.session_id !== sessionId) {
        localStorage.setItem('chatbot_session_id', response.data.session_id);
      }

      messages.value.push({
        role: 'model',
        content: response.data.reply,
        data: response.data.data,
        hideAvatar: false
      });
    } else {
      messages.value.push({ role: 'model', content: "Xin lỗi, đã xảy ra lỗi từ phía máy chủ.", hideAvatar: false });
    }
  } catch (error) {
    console.error('Chatbot error:', error);
    messages.value.push({ role: 'model', content: "Xin lỗi, kết nối bị gián đoạn. Vui lòng thử lại sau.", hideAvatar: false });
  } finally {
    isLoading.value = false;
    scrollToBottom();
  }
};

onMounted(async () => {
  // Chạy vòng lặp đổi chữ Bong Bóng mỗi 4 giây
  bubbleInterval = setInterval(() => {
    currentBubbleIndex.value = (currentBubbleIndex.value + 1) % bubbleTexts.length;
  }, 4000);

  // Lấy lịch sử chat từ máy chủ
  const sessionId = localStorage.getItem('chatbot_session_id');
  try {
    const response = await axios.get('/api/chatbot/history', {
      params: { session_id: sessionId }
    });

    if (response.data.status === 'success') {
      // Cập nhật lại session_id nếu backend trả về session mới (đã gộp với User đang đăng nhập)
      if (response.data.session_id && response.data.session_id !== sessionId) {
        localStorage.setItem('chatbot_session_id', response.data.session_id);
      }

      // Nếu có lịch sử tin nhắn thì ghi đè lên 2 tin nhắn chào mừng mặc định
      if (response.data.messages && response.data.messages.length > 0) {
        messages.value = response.data.messages;
        scrollToBottom();
      }
    }
  } catch (error) {
    console.error('Lỗi khi tải lịch sử chat:', error);
  }
});

onUnmounted(() => {
  if (bubbleInterval) clearInterval(bubbleInterval);
});
</script>

<style scoped>
/* Hiệu ứng trượt và mờ khi thay đổi text trong Bong bóng */
.text-fade-enter-active,
.text-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}

.text-fade-enter-from {
  opacity: 0;
  transform: translateY(4px);
}

.text-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

/* Hiệu ứng ẩn/hiện toàn bộ Bong bóng khi click nút X */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Custom Thanh cuộn ngang cho Thẻ Sản Phẩm */
.product-scrollbar::-webkit-scrollbar {
  height: 4px;
}

.product-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.product-scrollbar::-webkit-scrollbar-thumb {
  background: var(--color-outline-variant, #cbd5e1);
  border-radius: 4px;
}

.product-scrollbar:hover::-webkit-scrollbar-thumb {
  background: var(--color-primary, #94a3b8);
}
</style>
