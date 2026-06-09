<template>
  <div class="chat-page">
    <div class="chat-page-inner">
      <div
        class="chat-container bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-sm flex overflow-hidden">

        <!-- Cột Trái: Danh sách cuộc trò chuyện -->
        <div
          :class="['sidebar-panel border-r border-outline-variant flex flex-col', { 'hidden md:flex': !showSidebarOnMobile }]">
          <!-- Header tìm kiếm và bộ lọc -->
          <div class="pt-4 border-b border-outline-variant flex flex-col gap-3">
            <div class="px-4 flex flex-col gap-3">
              <h2 class="text-xl font-bold text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">chat</span>
                Hộp thư
              </h2>
              <div class="relative">
                <input v-model="searchQuery" type="text"
                  class="w-full bg-surface-container border border-outline-variant text-on-surface text-sm rounded-full pl-9 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                  placeholder="Tìm người dùng...">
                <span
                  class="material-symbols-outlined absolute left-3 top-2.5 text-sm text-on-surface-variant">search</span>
              </div>
            </div>

            <!-- Thanh filter nhãn dán -->
            <div class="relative group">
              <!-- Nút trượt trái -->
              <button v-show="canScrollLeft" @click="scrollFilters('left')"
                class="absolute left-1 top-0 bottom-3 my-auto w-7 h-7 rounded-full bg-surface-container-lowest shadow-md border border-outline-variant flex items-center justify-center text-on-surface opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-surface-container-low cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">chevron_left</span>
              </button>

              <div ref="filterContainer" @scroll="updateScrollState"
                class="px-4 pb-3 flex gap-2 overflow-x-auto scrollbar-hide shrink-0 scroll-smooth">
                <button @click="activeFilterLabel = 'all'"
                  :class="['px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors border cursor-pointer', activeFilterLabel === 'all' ? 'bg-surface-container-high border-on-surface text-on-surface' : 'bg-surface-container-lowest border-outline-variant text-on-surface-variant hover:bg-surface-container-low']">
                  Tất cả
                </button>
                <button @click="activeFilterLabel = 'unread'"
                  :class="['px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors border cursor-pointer', activeFilterLabel === 'unread' ? 'bg-surface-container-high border-on-surface text-on-surface' : 'bg-surface-container-lowest border-outline-variant text-on-surface-variant hover:bg-surface-container-low']">
                  Chưa đọc
                </button>
                <button v-for="label in allChatLabels" :key="label.id" @click="activeFilterLabel = label.id"
                  :class="['px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors border cursor-pointer', activeFilterLabel === label.id ? 'bg-surface-container-high border-on-surface text-on-surface' : 'bg-surface-container-lowest border-outline-variant text-on-surface-variant hover:bg-surface-container-low']">
                  {{ label.name }}
                </button>
              </div>

              <!-- Nút trượt phải -->
              <button v-show="canScrollRight" @click="scrollFilters('right')"
                class="absolute right-1 top-0 bottom-3 my-auto w-7 h-7 rounded-full bg-surface-container-lowest shadow-md border border-outline-variant flex items-center justify-center text-on-surface opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-surface-container-low cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">chevron_right</span>
              </button>
            </div>
          </div>

          <!-- Danh sách cuộc trò chuyện -->
          <div class="conversations-list flex-1 overflow-y-auto divide-y divide-outline-variant/50">
            <div v-if="chatStore.loading && chatStore.conversations.length === 0"
              class="p-6 text-center text-on-surface-variant flex flex-col items-center gap-2">
              <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
              <span class="text-sm">Đang tải cuộc trò chuyện...</span>
            </div>

            <div v-else-if="filteredConversations.length === 0"
              class="p-8 text-center text-on-surface-variant flex flex-col items-center gap-2">
              <span class="material-symbols-outlined text-4xl opacity-40">chat_bubble_outline</span>
              <span class="text-sm font-medium">Không tìm thấy cuộc trò chuyện nào</span>
            </div>

            <div v-else v-for="conv in filteredConversations" :key="conv.id" @click="selectConversation(conv)"
              :class="['conversation-item p-4 flex gap-3 cursor-pointer hover:bg-surface-container-low transition-all items-center relative group', { 'bg-surface-container-high': chatStore.activeConversation && Number(chatStore.activeConversation.id) === Number(conv.id) }]">
              <!-- Avatar -->
              <div class="relative shrink-0">
                <img :src="conv.partner.avatar" alt="Avatar"
                  class="w-12 h-12 rounded-full object-cover border border-outline-variant">
                <span v-if="conv.unread_messages_count > 0"
                  class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-error text-[10px] text-on-error font-bold border-2 border-surface-container-lowest">
                  {{ conv.unread_messages_count }}
                </span>
              </div>

              <!-- Nội dung tóm tắt -->
              <div class="flex-1 min-w-0">
                <!-- Nhãn phân loại -->
                <div v-if="conv.user_labels && conv.user_labels.length > 0" class="flex gap-1 mb-1 flex-wrap">
                  <span v-for="label in conv.user_labels" :key="label.id"
                    class="px-2 py-0.5 rounded-full text-[11px] font-medium text-on-surface bg-surface-container-high flex items-center gap-1 w-max">
                    <span class="material-symbols-outlined !text-[14px] -rotate-45"
                      :style="{ color: label.color_code, fontVariationSettings: '\'FILL\' 1' }">sell</span>
                    {{ label.name }}
                  </span>
                </div>

                <div class="flex justify-between items-baseline mb-1">
                  <h4 class="font-bold text-sm text-on-surface truncate pr-2">{{ conv.partner.name }}</h4>
                  <div class="relative flex items-center h-5 shrink-0">
                    <span class="text-[10px] text-on-surface-variant transition-opacity group-hover:opacity-0">{{
                      formatTime(conv.latest_message ?
                        conv.latest_message.created_at : conv.updated_at) }}</span>

                    <!-- Dấu 3 chấm Gắn phân loại -->
                    <button @click.stop="toggleLabelPopover(conv.id)"
                      class="btn-toggle-popover opacity-0 group-hover:opacity-100 absolute right-0 top-1/2 -translate-y-1/2 p-1 text-on-surface-variant hover:text-on-surface transition-all z-10 flex items-center justify-center cursor-pointer">
                      <span class="material-symbols-outlined text-[18px]">more_vert</span>
                    </button>
                  </div>
                </div>
                <p class="text-xs text-on-surface-variant truncate pr-4"
                  :class="{ 'font-bold text-on-surface': conv.unread_messages_count > 0 }">
                  {{ formatLatestMessageSnippet(conv.latest_message) }}
                </p>
              </div>

              <!-- Bài viết thu nhỏ liên quan -->
              <div v-if="conv.post"
                class="shrink-0 w-10 h-10 rounded-lg overflow-hidden border border-outline-variant/60 mr-4"
                title="Tin đăng đang trao đổi">
                <img :src="conv.post.image || 'https://images.unsplash.com/photo-1584438784894-089d6a128f3e?q=80&w=100'"
                  alt="Post Thumb" class="w-full h-full object-cover">
              </div>

              <!-- Popover Gắn phân loại -->
              <div v-if="activePopover === conv.id"
                class="label-popover absolute right-6 top-10 z-50 w-64 bg-surface-container-lowest rounded-xl shadow-lg border border-outline-variant p-0 flex flex-col overflow-hidden"
                @click.stop>
                <div
                  class="p-3 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
                  <span class="font-bold text-sm">Gắn phân loại</span>
                  <button @click.stop="activePopover = null"
                    class="text-on-surface-variant hover:text-error cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                  </button>
                </div>
                <div class="p-2 flex flex-col gap-1 max-h-60 overflow-y-auto">
                  <label v-for="label in allChatLabels" :key="label.id"
                    class="flex items-center gap-3 p-2 hover:bg-surface-container-low rounded-lg cursor-pointer">
                    <input type="checkbox" :value="label.id" v-model="selectedLabels"
                      class="w-4 h-4 rounded text-primary focus:ring-primary">
                    <span class="material-symbols-outlined text-[20px]"
                      :style="{ color: label.color_code, fontVariationSettings: '\'FILL\' 1' }">sell</span>
                    <span class="text-sm flex-1">{{ label.name }}</span>
                  </label>
                </div>
                <div class="p-2 border-t border-outline-variant flex gap-2">
                  <button @click.stop="openManageLabels"
                    class="flex-1 py-1.5 bg-surface-container text-on-surface rounded-lg text-sm font-bold hover:bg-surface-container-high transition-colors cursor-pointer">
                    Quản lý
                  </button>
                  <button @click.stop="saveConversationLabels(conv.id)"
                    class="flex-1 py-1.5 bg-primary text-on-primary rounded-lg text-sm font-bold hover:bg-primary-container hover:text-on-primary-container transition-colors cursor-pointer">
                    Lưu
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cột Phải: Bong bóng tin nhắn & Khung chat -->
        <div
          :class="['chat-panel flex-1 flex flex-col bg-surface-container-lowest', { 'hidden md:flex': showSidebarOnMobile, 'w-full': !showSidebarOnMobile }]">

          <!-- Khung Chat Trống (Khi chưa chọn cuộc trò chuyện) -->
          <div v-if="!chatStore.activeConversation"
            class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-surface-container-lowest">
            <img src="https://illustrations.popsy.co/amber/online-shopping.svg" alt="Chat Illustration"
              class="w-64 h-64 md:w-80 md:h-80 object-contain mb-4 opacity-95 transition-transform duration-500 hover:scale-105 select-none pointer-events-none">
            <h3 class="text-xl md:text-2xl font-extrabold text-on-surface mb-2 tracking-tight">Tích cực chat, chốc lát
              chốt đơn</h3>
          </div>

          <!-- Khung Chat Hoạt Động -->
          <template v-else>
            <!-- Header Khung Chat -->
            <div
              class="p-4 border-b border-outline-variant bg-surface-container-lowest flex items-center justify-between gap-3 shrink-0">
              <div class="flex items-center gap-3 min-w-0">
                <!-- Nút quay lại trên mobile -->
                <button @click="backToSidebar"
                  class="md:hidden p-2 -ml-2 text-on-surface hover:bg-surface-container rounded-full shrink-0">
                  <span class="material-symbols-outlined">arrow_back</span>
                </button>

                <img :src="chatStore.activeConversation.partner.avatar" alt="Avatar"
                  class="w-10 h-10 rounded-full object-cover border border-outline-variant shrink-0">
                <div class="min-w-0">
                  <h3 class="font-bold text-on-surface text-sm sm:text-base truncate">{{
                    chatStore.activeConversation.partner.name }}</h3>
                  <button @click="viewPartnerProfile"
                    class="text-xs text-primary hover:underline font-medium text-left cursor-pointer">Xem trang cá
                    nhân</button>
                </div>
              </div>

            </div>

            <!-- Khung nội dung Tin nhắn -->
            <div class="chat-messages flex-1 p-4 overflow-y-auto bg-surface-container/10 flex flex-col gap-3"
              ref="messagesContainer">
              <div v-if="loadingMessages"
                class="py-8 text-center text-on-surface-variant flex flex-col items-center gap-2">
                <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs">Đang tải lịch sử tin nhắn...</span>
              </div>

              <template v-else>
                <div v-if="messages.length === 0"
                  class="py-12 text-center text-on-surface-variant flex flex-col items-center gap-2">
                  <span class="material-symbols-outlined text-4xl opacity-30">chat</span>
                  <span class="text-xs font-semibold">Chưa có tin nhắn. Hãy bắt đầu trò chuyện ngay!</span>
                </div>

                <template v-else>
                  <template v-for="(msg, index) in messages" :key="msg.id">
                    <!-- Date Separator -->
                    <div v-if="shouldShowDateSeparator(index)"
                      class="flex items-center justify-center my-4 w-full px-2">
                      <div class="flex-1 h-px bg-outline-variant/30"></div>
                      <span class="mx-4 text-xs text-on-surface-variant font-medium">{{
                        formatDateSeparator(msg.created_at) }}</span>
                      <div class="flex-1 h-px bg-outline-variant/30"></div>
                    </div>

                    <div
                      :class="['message-row flex flex-col w-full mb-1', msg.sender_id === authStore.user?.id ? 'items-end' : 'items-start']">
                      <!-- WIDGET SẢN PHẨM ĐÍNH KÈM TRONG TIN NHẮN -->
                      <div v-if="msg.post" :id="'widget-post-' + msg.post.id"
                        class="mb-2 max-w-[85%] sm:max-w-[70%] bg-surface-container-lowest border border-outline-variant/60 rounded-xl overflow-hidden shadow-sm hover:shadow transition-shadow">
                        <div class="flex items-center gap-3 p-3 bg-surface-container-low cursor-pointer"
                          @click="$router.push(`/post/${msg.post.slug}`)">
                          <img
                            :src="msg.post.image || 'https://images.unsplash.com/photo-1584438784894-089d6a128f3e?q=80&w=100'"
                            alt="Post"
                            class="w-12 h-12 rounded-lg object-cover border border-outline-variant/50 shrink-0">
                          <div class="text-left min-w-0 pr-1">
                            <p class="text-sm font-bold text-on-surface truncate leading-tight">{{ msg.post.title }}</p>
                            <p class="text-xs text-error font-extrabold leading-none mt-1">{{
                              formatPrice(msg.post.price) }}đ</p>
                          </div>
                        </div>
                      </div>

                      <div class="flex items-center gap-2 group max-w-[85%] sm:max-w-[75%]"
                        :class="msg.sender_id === authStore.user?.id ? 'flex-row-reverse' : 'flex-row'">
                        <div class="message-bubble-wrapper flex flex-col gap-0.5"
                          :class="msg.sender_id === authStore.user?.id ? 'items-end' : 'items-start'">
                          <div class="flex flex-col gap-1"
                            :class="msg.sender_id === authStore.user?.id ? 'items-end' : 'items-start'">
                            <!-- Ảnh đính kèm -->
                            <img v-if="msg.image_path" :src="msg.image_path" alt="Image"
                              class="max-w-full max-h-64 rounded-lg object-contain cursor-pointer border border-outline-variant/30 shadow-sm"
                              @click="window.open(msg.image_path, '_blank')">

                            <!-- Bong bóng văn bản -->
                            <div v-if="msg.message_text" :class="[
                              'message-bubble px-4 py-2.5 text-sm transition-all shadow-sm break-words inline-block',
                              msg.sender_id === authStore.user?.id
                                ? 'bg-primary text-on-primary rounded-2xl rounded-tr-none'
                                : 'bg-surface-container-high text-on-surface rounded-2xl rounded-tl-none'
                            ]">
                              {{ msg.message_text }}
                            </div>
                          </div>

                          <!-- Thời gian gửi tinh tế -->
                          <span :class="[
                            'text-[9px] text-on-surface-variant px-1',
                            msg.sender_id === authStore.user?.id ? 'text-right' : 'text-left'
                          ]">
                            {{ formatMessageTime(msg.created_at) }}
                          </span>
                        </div>

                        <!-- Hành động tin nhắn (hiển thị khi hover) -->
                        <div
                          class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 shrink-0">
                          <button @click="unsendMessage(msg.id)" v-if="msg.sender_id === authStore.user?.id"
                            type="button"
                            class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-full flex items-center cursor-pointer"
                            title="Thu hồi tin nhắn">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </template>
                </template>
              </template>
            </div>

            <!-- Khung Nhập tin nhắn và gửi -->
            <div class="chat-input-area p-4 border-t border-outline-variant bg-surface-container-lowest shrink-0">
              <!-- Thanh đính kèm tư vấn sản phẩm (Chuẩn Chợ Tốt) -->
              <div v-if="attachedPost"
                class="attached-post-bar flex items-center justify-between gap-3 p-3 bg-primary/5 border border-primary/20 rounded-xl mb-3 animate-fade-in">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="relative shrink-0 w-12 h-12 rounded-lg overflow-hidden border border-primary/10">
                    <img
                      :src="attachedPost.image || 'https://images.unsplash.com/photo-1584438784894-089d6a128f3e?q=80&w=100'"
                      alt="Attached Post" class="w-full h-full object-cover">
                    <span
                      class="absolute top-0 left-0 bg-primary text-on-primary text-[8px] font-bold px-1 py-0.5 rounded-br-md leading-none">ĐÍNH
                      KÈM</span>
                  </div>
                  <div class="min-w-0 text-left">
                    <p class="text-xs font-bold text-on-surface truncate leading-snug">{{ attachedPost.title }}</p>
                    <p class="text-xs text-error font-extrabold mt-0.5">{{ formatPrice(attachedPost.price) }}đ</p>
                  </div>
                </div>
                <button type="button" @click="attachedPost = null"
                  class="shrink-0 p-1.5 hover:bg-surface-container rounded-full text-on-surface-variant hover:text-error transition-all flex items-center justify-center cursor-pointer"
                  title="Hủy đính kèm">
                  <span class="material-symbols-outlined text-sm font-bold">close</span>
                </button>
              </div>

              <!-- Khung xem trước ảnh -->
              <div v-if="imagePreviewUrl" class="mb-3 relative inline-block animate-fade-in">
                <img :src="imagePreviewUrl"
                  class="max-h-32 rounded-lg border border-outline-variant object-cover shadow-sm">
                <button @click="removeSelectedImage" type="button"
                  class="absolute -top-1.5 -right-1.5 bg-error text-on-error rounded-full w-5 h-5 flex items-center justify-center cursor-pointer shadow-sm">
                  <span class="material-symbols-outlined font-bold block" style="font-size: 12px;">close</span>
                </button>
              </div>

              <form @submit.prevent="sendNewMessage" class="flex gap-2 items-end relative">
                <input type="file" ref="fileInput" @change="handleFileChange" accept="image/*" class="hidden">
                <!-- Nút đính kèm ảnh -->
                <button type="button" @click="triggerFileInput"
                  class="p-2 text-on-surface-variant hover:text-primary transition-colors shrink-0 cursor-pointer"
                  title="Đính kèm ảnh">
                  <span class="material-symbols-outlined">image</span>
                </button>

                <textarea v-model="newMessageText" @keydown.enter.exact.prevent="sendNewMessage"
                  class="flex-1 bg-surface-container border border-outline-variant text-on-surface text-sm rounded-2xl px-4 py-2.5 max-h-24 resize-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all leading-relaxed"
                  placeholder="Nhập tin nhắn..." rows="1" ref="messageTextarea"></textarea>

                <button type="submit" :disabled="(!newMessageText.trim() && !selectedImage) || sending"
                  class="p-2.5 bg-primary text-on-primary hover:bg-primary-container disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary rounded-xl shadow-sm hover:shadow transition-all shrink-0 flex items-center justify-center cursor-pointer">
                  <span class="material-symbols-outlined text-sm font-bold">send</span>
                </button>
              </form>
            </div>
          </template>
        </div>

      </div>
    </div>

    <!-- Modal Quản lý phân loại -->
    <div v-if="isManagingLabels"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 animate-fade-in"
      @click.self="isManagingLabels = false">
      <div
        class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-xl flex flex-col overflow-hidden max-h-[90vh]">
        <div class="p-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
          <h3 class="font-bold text-lg">Quản lý phân loại</h3>
          <button @click="isManagingLabels = false"
            class="p-1 text-on-surface-variant hover:text-error transition-colors cursor-pointer">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3">
          <div v-for="label in allChatLabels" :key="label.id"
            class="flex items-center gap-3 p-3 bg-surface-container-low border border-outline-variant/50 rounded-xl">
            <span class="material-symbols-outlined text-[24px]"
              :style="{ color: label.color_code, fontVariationSettings: '\'FILL\' 1' }">sell</span>
            <div class="flex-1 min-w-0">
              <p class="font-bold text-sm truncate">{{ label.name }}</p>
              <p class="text-xs text-on-surface-variant">{{ label.conversation_labels_count || 0 }} đoạn chat</p>
            </div>

            <div v-if="!label.is_default" class="flex items-center gap-1 shrink-0">
              <button @click="editManageLabel(label)"
                class="p-2 text-on-surface-variant hover:text-primary transition-colors cursor-pointer" title="Sửa">
                <span class="material-symbols-outlined text-[18px]">edit</span>
              </button>
              <button @click="deleteManageLabel(label.id)"
                class="p-2 text-on-surface-variant hover:text-error transition-colors cursor-pointer" title="Xóa">
                <span class="material-symbols-outlined text-[18px]">delete</span>
              </button>
            </div>
          </div>
        </div>

        <div class="p-4 border-t border-outline-variant bg-surface-container flex flex-col gap-3">
          <h4 class="font-bold text-sm">{{ managingLabelData.id ? 'Sửa nhãn' : 'Tạo mới' }}</h4>
          <div class="flex gap-2">
            <input type="text" v-model="managingLabelData.name" placeholder="Tên nhãn dán"
              class="flex-1 px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none">
            <input type="color" v-model="managingLabelData.color_code"
              class="color-picker-input w-10 h-10 rounded-lg cursor-pointer bg-transparent border-0 p-0 shrink-0">
          </div>
          <div class="flex gap-2 justify-end mt-2">
            <button v-if="managingLabelData.id" @click="resetManagingLabel"
              class="px-4 py-2 text-sm font-bold text-on-surface hover:bg-surface-container-high rounded-lg transition-colors">
              Hủy
            </button>
            <button @click="saveManageLabel"
              class="px-4 py-2 bg-primary text-on-primary text-sm font-bold rounded-lg hover:bg-primary-container transition-colors cursor-pointer">
              Lưu nhãn
            </button>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

<script setup>
import { toast, confirmDialog } from '../utils/alert';

import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useChatStore } from '../stores/chat';

import axios from 'axios';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const chatStore = useChatStore();

const searchQuery = ref('');
const messages = ref([]);
const newMessageText = ref('');
const loadingMessages = ref(false);
const sending = ref(false);
const showSidebarOnMobile = ref(true);

const activeFilterLabel = ref('all');
const filterContainer = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

const updateScrollState = () => {
  if (filterContainer.value) {
    const el = filterContainer.value;
    canScrollLeft.value = el.scrollLeft > 0;
    // Kiểm tra còn dư khoảng cuộn phải hay không (trừ đi 1px dung sai)
    canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 1;
  }
};

const scrollFilters = (direction) => {
  if (filterContainer.value) {
    const scrollAmount = 200; // Số pixel cuộn mỗi lần nhấn
    if (direction === 'left') {
      filterContainer.value.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
      filterContainer.value.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  }
};

const allChatLabels = ref([]);
const activePopover = ref(null);
const selectedLabels = ref([]);
const isManagingLabels = ref(false);
const managingLabelData = ref({ id: null, name: '', color_code: '#ef4444' });

const fetchChatLabels = async () => {
  try {
    const res = await axios.get('/api/chat-labels');
    allChatLabels.value = res.data.data || [];
    nextTick(() => {
      updateScrollState();
    });
  } catch (error) {
    console.error('Lỗi khi tải danh sách nhãn:', error);
  }
};

const toggleLabelPopover = (convId) => {
  if (activePopover.value === convId) {
    activePopover.value = null;
  } else {
    activePopover.value = convId;
    const conv = chatStore.conversations.find(c => c.id === convId);
    selectedLabels.value = conv && conv.user_labels ? conv.user_labels.map(l => l.id) : [];
  }
};

const saveConversationLabels = async (convId) => {
  try {
    await axios.post(`/api/conversations/${convId}/labels`, { label_ids: selectedLabels.value });
    const conv = chatStore.conversations.find(c => c.id === convId);
    if (conv) {
      conv.user_labels = allChatLabels.value.filter(l => selectedLabels.value.includes(l.id));
    }
    toast('Đã cập nhật phân loại', 'success');
    activePopover.value = null;
  } catch (error) {
    toast('Lỗi khi cập nhật phân loại', 'error');
  }
};

const openManageLabels = () => {
  isManagingLabels.value = true;
  activePopover.value = null;
  resetManagingLabel();
};

const resetManagingLabel = () => {
  managingLabelData.value = { id: null, name: '', color_code: '#ef4444' };
};

const saveManageLabel = async () => {
  if (!managingLabelData.value.name) {
    toast('Vui lòng nhập tên nhãn', 'error');
    return;
  }
  try {
    if (managingLabelData.value.id) {
      // Edit
      await axios.put(`/api/chat-labels/${managingLabelData.value.id}`, managingLabelData.value);
      toast('Cập nhật nhãn thành công', 'success');
    } else {
      // Create
      await axios.post('/api/chat-labels', managingLabelData.value);
      toast('Tạo nhãn thành công', 'success');
    }
    resetManagingLabel();
    fetchChatLabels();
  } catch (error) {
    const msg = error.response?.data?.message || 'Lỗi khi lưu nhãn';
    toast(msg, 'error');
  }
};

const deleteManageLabel = async (id) => {
  const isConfirmed = await confirmDialog('Xóa nhãn phân loại', 'Bạn có chắc chắn muốn xóa nhãn này? Hành động này không thể hoàn tác.', 'Xóa', 'Hủy');
  if (!isConfirmed) return;
  try {
    await axios.delete(`/api/chat-labels/${id}`);
    toast('Xóa nhãn thành công', 'success');
    fetchChatLabels();
  } catch (error) {
    const msg = error.response?.data?.message || 'Lỗi khi xóa nhãn';
    toast(msg, 'error');
  }
};

const editManageLabel = (label) => {
  managingLabelData.value = { ...label };
};

const closePopoverOnOutsideClick = (e) => {
  if (!e.target.closest('.label-popover') && !e.target.closest('.btn-toggle-popover')) {
    activePopover.value = null;
  }
};




const messagesContainer = ref(null);
const messageTextarea = ref(null);
const fileInput = ref(null);

const selectedImage = ref(null);
const imagePreviewUrl = ref(null);

// Lưu trữ thông tin sản phẩm chuẩn bị đính kèm gửi đi
const attachedPost = ref(null);

// Lọc các cuộc hội thoại dựa trên thanh tìm kiếm và bộ lọc nhãn
const filteredConversations = computed(() => {
  let result = chatStore.conversations;

  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    result = result.filter(c =>
      c.partner.name.toLowerCase().includes(query)
    );
  }

  if (activeFilterLabel.value === 'unread') {
    result = result.filter(c => c.unread_messages_count > 0);
  } else if (activeFilterLabel.value !== 'all') {
    result = result.filter(c =>
      c.user_labels && c.user_labels.some(l => l.id === activeFilterLabel.value)
    );
  }

  return result;
});

// Chuyển đổi hiển thị sang khung chat trên di động
const selectConversation = async (conv) => {
  chatStore.setActiveConversation(conv);
  showSidebarOnMobile.value = false;
};

// Quay lại danh sách hội thoại trên mobile
const backToSidebar = () => {
  showSidebarOnMobile.value = true;
};

// Xem trang cá nhân công khai của đối phương
const viewPartnerProfile = () => {
  if (chatStore.activeConversation) {
    router.push(`/seller/${chatStore.activeConversation.partner.id}`);
  }
};

// Tải danh sách tin nhắn của cuộc hội thoại đang mở
const fetchMessages = async (convId) => {
  loadingMessages.value = true;
  messages.value = [];
  try {
    const response = await axios.get(`/api/conversations/${convId}/messages`);
    if (response.data.success) {
      messages.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi khi tải lịch sử tin nhắn:', error);
  } finally {
    loadingMessages.value = false;
    // Đợi DOM render xong danh sách tin nhắn rồi mới cuộn xuống dưới
    await nextTick();
    scrollToBottom();

    // Cuộn thêm lần nữa sau khi hình ảnh có thể đã load xong để tránh bị đẩy ngược lên
    setTimeout(scrollToBottom, 500);
  }
};

// Gửi tin nhắn mới
const sendNewMessage = async () => {
  if ((!newMessageText.value.trim() && !selectedImage.value) || sending.value || !chatStore.activeConversation) return;

  const text = newMessageText.value.trim();
  sending.value = true;

  // Lấy ID sản phẩm đính kèm nếu có
  const attachPostId = attachedPost.value ? attachedPost.value.id : null;

  const formData = new FormData();
  if (text) formData.append('message_text', text);
  if (attachPostId) formData.append('post_id', attachPostId);
  if (selectedImage.value) formData.append('image', selectedImage.value);

  try {
    const response = await axios.post(`/api/conversations/${chatStore.activeConversation.id}/messages`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.success) {
      // Đẩy tin nhắn vừa gửi vào danh sách hiển thị
      messages.value.push(response.data.data);

      // Cập nhật sản phẩm liên kết của cuộc hội thoại hiện tại (tùy chọn)
      if (response.data.attached_post) {
        // chatStore.activeConversation.post = response.data.attached_post;
      }

      // Đồng bộ tin nhắn mới nhất
      const currentConv = chatStore.conversations.find(c => Number(c.id) === Number(chatStore.activeConversation.id));
      if (currentConv) {
        currentConv.latest_message = {
          id: response.data.data.id,
          message_text: response.data.data.message_text,
          image_path: response.data.data.image_path,
          sender_id: response.data.data.sender_id,
          is_read: true,
          created_at: response.data.data.created_at
        };
        // Đẩy cuộc hội thoại này lên đầu danh sách
        chatStore.conversations = [
          currentConv,
          ...chatStore.conversations.filter(c => Number(c.id) !== Number(currentConv.id))
        ];
      }

      // Xóa sản phẩm đính kèm và cập nhật query params URL
      attachedPost.value = null;
      if (route.query.attach_post_id) {
        const query = { ...route.query };
        delete query.attach_post_id;
        router.replace({ query });
      }

      // Xóa form và ảnh đã chọn
      newMessageText.value = '';
      removeSelectedImage();

      nextTick(() => {
        scrollToBottom();
      });
    }
  } catch (error) {
    console.error('Gửi tin nhắn thất bại:', error);
    if (error.response?.data?.message) {
      toast(error.response.data.message, 'error');
    }
  } finally {
    sending.value = false;
    // Đặt lại con trỏ chuột vào textarea
    nextTick(() => {
      messageTextarea.value?.focus();
    });
  }
};

// Tự động cuộn khung chat xuống dưới cùng
const scrollToBottom = () => {
  if (messagesContainer.value) {
    // Delay nhẹ để đảm bảo DOM và layout đã cập nhật hoàn toàn
    setTimeout(() => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      }
    }, 50);
  }
};

const handleIncomingMessage = (e) => {
  const message = e.detail;
  if (chatStore.activeConversation && Number(chatStore.activeConversation.id) === Number(message.conversation_id)) {
    // Kiểm tra trùng lặp để không thêm cùng 1 tin nhắn nhiều lần do nhận nhiều kênh websocket
    const isExists = messages.value.find(m => Number(m.id) === Number(message.id));
    if (!isExists) {
      messages.value.push(message);
      nextTick(() => {
        scrollToBottom();
      });
    }
  }
};

const triggerFileInput = () => {
  if (fileInput.value) {
    fileInput.value.click();
  }
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 5 * 1024 * 1024) { // Tối đa 5MB
      toast('Kích thước ảnh tối đa là 5MB', 'error');
      if (fileInput.value) fileInput.value.value = '';
      return;
    }
    selectedImage.value = file;
    imagePreviewUrl.value = URL.createObjectURL(file);
    // Đưa focus lại textarea
    nextTick(() => {
      messageTextarea.value?.focus();
    });
  }
};

const removeSelectedImage = () => {
  selectedImage.value = null;
  imagePreviewUrl.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

// Định dạng thời gian cập nhật ở danh sách hội thoại
const formatTime = (timeStr) => {
  if (!timeStr) return '';
  const date = new Date(timeStr);
  const now = new Date();

  if (date.toDateString() === now.toDateString()) {
    return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
  }

  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
};

// Định dạng tóm tắt tin nhắn cuối cùng (hiển thị ở sidebar)
const formatLatestMessageSnippet = (msg) => {
  if (!msg) return 'Chưa có tin nhắn';
  const isMine = msg.sender_id === authStore.user?.id;
  const prefix = isMine ? 'Bạn: ' : '';

  if (msg.message_text) {
    return prefix + msg.message_text;
  }

  if (msg.image_path) {
    return prefix + (isMine ? 'đã gửi một hình ảnh' : 'Đã gửi một hình ảnh');
  }

  return prefix + 'Đã gửi một tệp';
};

// Hiển thị thanh chia ngày
const shouldShowDateSeparator = (index) => {
  if (index === 0) return true;
  const currentDate = new Date(messages.value[index].created_at).toDateString();
  const previousDate = new Date(messages.value[index - 1].created_at).toDateString();
  return currentDate !== previousDate;
};

// Định dạng ngày hiển thị ở thanh chia
const formatDateSeparator = (timeStr) => {
  if (!timeStr) return '';
  const date = new Date(timeStr);
  const today = new Date();

  if (date.toDateString() === today.toDateString()) return 'Hôm nay';

  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  if (date.toDateString() === yesterday.toDateString()) return 'Hôm qua';

  const days = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
  const dayName = days[date.getDay()];

  // Format dd/MM/yyyy
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();

  return `${dayName}, ${day}/${month}/${year}`;
};

// Định dạng thời gian tin nhắn cụ thể
const formatMessageTime = (timeStr) => {
  if (!timeStr) return '';
  const date = new Date(timeStr);
  return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

// Định dạng giá tiền tệ
const formatPrice = (value) => {
  if (!value) return '0';
  return parseFloat(value).toLocaleString('vi-VN');
};

// Kiểm tra và tải thông tin sản phẩm đính kèm từ URL query
const checkAttachPost = async () => {
  const attachPostId = route.query.attach_post_id;
  if (attachPostId) {
    // Chỉ tải nếu chưa được đính kèm trước đó hoặc bài đính kèm khác với sản phẩm đang liên kết của cuộc hội thoại
    const activePostId = chatStore.activeConversation?.post?.id;
    if (!activePostId || Number(activePostId) !== Number(attachPostId)) {
      try {
        const response = await axios.get(`/api/posts/id/${attachPostId}`);
        if (response.data.success) {
          attachedPost.value = response.data.data;
        }
      } catch (error) {
        console.error('Lỗi khi tải thông tin sản phẩm đính kèm:', error);
      }
    } else {
      attachedPost.value = null;
    }
  } else {
    attachedPost.value = null;
  }
};

// Theo dõi thay đổi cuộc trò chuyện đang mở để tự động tải tin nhắn và kiểm tra đính kèm
watch(() => chatStore.activeConversation, async (newConv) => {
  if (newConv) {
    await fetchMessages(newConv.id);
    await checkAttachPost();
  } else {
    messages.value = [];
    attachedPost.value = null;
  }
});

// Lắng nghe thay đổi của route query để tự động cập nhật cuộc hội thoại và sản phẩm đính kèm
watch(() => route.query, async (newQuery) => {
  const queryConvId = newQuery.conversation_id;
  if (queryConvId) {
    const existingConv = chatStore.conversations.find(c => Number(c.id) === Number(queryConvId));
    if (existingConv) {
      chatStore.setActiveConversation(existingConv);
      showSidebarOnMobile.value = false;
    }
  } else {
    chatStore.setActiveConversation(null);
    showSidebarOnMobile.value = true;
  }
  await checkAttachPost();
}, { deep: true });

const handleMessageDeleted = (e) => {
  const messageId = e.detail;
  messages.value = messages.value.filter(m => Number(m.id) !== Number(messageId));
};

const canUnsend = (createdAt) => {
  if (!createdAt) return false;
  const diffInMs = new Date() - new Date(createdAt);
  return diffInMs < 10 * 60 * 1000;
};

const unsendMessage = async (id) => {
  const isConfirmed = await confirmDialog('Thu hồi tin nhắn', 'Bạn có chắc chắn muốn thu hồi tin nhắn này?', 'Thu hồi', 'Hủy');
  if (!isConfirmed) return;
  try {
    const res = await axios.delete(`/api/messages/${id}`);
    if (res.data.success) {
      toast('Đã thu hồi tin nhắn', 'success');
      messages.value = messages.value.filter(m => Number(m.id) !== Number(id));
      chatStore.fetchConversations();
    }
  } catch (error) {
    if (error.response?.data?.message) {
      toast(error.response.data.message, 'error');
    } else {
      toast('Thu hồi tin nhắn thất bại', 'error');
    }
  }
};

// Khởi tạo trang
onMounted(async () => {
  fetchChatLabels();
  document.addEventListener('click', closePopoverOnOutsideClick);

  // Tải danh sách các cuộc hội thoại
  await chatStore.fetchConversations();

  // Đăng ký lắng nghe sự kiện khi có tin nhắn real-time tới
  window.addEventListener('new-message-received', handleIncomingMessage);
  window.addEventListener('message-deleted-received', handleMessageDeleted);
  document.addEventListener('click', closePopoverOnOutsideClick);

  // Nhận tham số conversation_id hoặc post_id từ query (ví dụ khi nhấn "Nhắn tin ngay" từ trang chi tiết bài đăng)
  const queryConvId = route.query.conversation_id;
  if (queryConvId) {
    const existingConv = chatStore.conversations.find(c => Number(c.id) === Number(queryConvId));
    if (existingConv) {
      chatStore.setActiveConversation(existingConv);
      showSidebarOnMobile.value = false;
    } else {
      // Nếu chưa có trong danh sách nạp trước, tải danh sách hội thoại rồi chọn
      await chatStore.fetchConversations();
      const updatedConv = chatStore.conversations.find(c => c.id === Number(queryConvId));
      if (updatedConv) {
        chatStore.setActiveConversation(updatedConv);
        showSidebarOnMobile.value = false;
      }
    }
  } else {
    // Nếu vào trang chat mà không có query id (từ Header), luôn hiển thị màn hình trống chuẩn Chợ Tốt
    chatStore.setActiveConversation(null);
    showSidebarOnMobile.value = true;
  }

  // Kiểm tra thông tin sản phẩm đính kèm
  await checkAttachPost();
});

onUnmounted(() => {
  document.removeEventListener('click', closePopoverOnOutsideClick);
  window.removeEventListener('new-message-received', handleIncomingMessage);
  window.removeEventListener('message-deleted-received', handleMessageDeleted);
  if (chatStore.activeConversation && window.Echo) {
    window.Echo.leaveChannel(`chat.${chatStore.activeConversation.id}`);
  }
  chatStore.setActiveConversation(null);
});
</script>

<style scoped>
/* Khung chat fill toàn bộ chiều cao còn lại sau Header (64px) */
.chat-page {
  height: calc(100vh - 64px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Giữ padding 2 bên giống các trang khác, fill chiều cao */
.chat-page-inner {
  max-width: 80rem;
  /* max-w-7xl */
  width: 100%;
  margin-left: auto;
  margin-right: auto;
  padding-left: 1rem;
  /* px-4 */
  padding-right: 1rem;
  padding-top: 1.5rem;
  /* py-6 */
  padding-bottom: 1.5rem;
  height: 100%;
  display: flex;
  flex-direction: column;
}

@media (min-width: 640px) {
  .chat-page-inner {
    padding-left: 1.5rem;
    /* sm:px-6 */
    padding-right: 1.5rem;
  }
}

@media (min-width: 1024px) {
  .chat-page-inner {
    padding-left: 2rem;
    /* lg:px-8 */
    padding-right: 2rem;
  }
}

.chat-container {
  height: 100%;
  width: 100%;
}

/* Cột sidebar cố định chiều rộng, tự cuộn nội dung bên trong */
.sidebar-panel {
  width: 300px;
  min-width: 280px;
  max-width: 340px;
  flex-shrink: 0;
}

@media (min-width: 1024px) {
  .sidebar-panel {
    width: 340px;
    max-width: 380px;
  }
}

/* Chat panel chiếm phần còn lại và tự flex column */
.chat-panel {
  height: 100%;
  overflow: hidden;
}

.animate-fade-in {
  animation: fadeIn 0.25s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Tùy chỉnh thanh cuộn đẹp mắt cho danh sách tin nhắn */
.chat-messages::-webkit-scrollbar,
.conversations-list::-webkit-scrollbar {
  width: 5px;
}

.chat-messages::-webkit-scrollbar-track,
.conversations-list::-webkit-scrollbar-track {
  background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb,
.conversations-list::-webkit-scrollbar-thumb {
  background: var(--color-outline-variant);
  border-radius: 999px;
}

.chat-messages::-webkit-scrollbar-thumb:hover,
.conversations-list::-webkit-scrollbar-thumb:hover {
  background: var(--color-outline);
}

/* Custom style cho ô chọn màu */
.color-picker-input {
  appearance: none;
  border: none;
}

.color-picker-input::-webkit-color-swatch-wrapper {
  padding: 0;
}

.color-picker-input::-webkit-color-swatch {
  border: none;
  border-radius: 0.5rem;
}

/* Ẩn thanh cuộn của phần filter */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
