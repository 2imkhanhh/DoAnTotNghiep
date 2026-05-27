<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 transition-opacity">
    <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
      <!-- Header -->
      <div class="p-4 border-b border-outline-variant flex items-center justify-between">
        <h3 class="text-lg font-bold text-on-surface">Đánh giá người bán</h3>
        <button @click="close"
          class="p-2 hover:bg-surface-container rounded-full text-on-surface-variant transition-colors">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Body -->
      <div class="p-5 flex flex-col gap-4">
        <!-- Rating Stars -->
        <div class="flex flex-col items-center gap-2">
          <span class="text-sm font-medium text-on-surface-variant">Chất lượng sản phẩm & Dịch vụ</span>
          <div class="flex gap-2">
            <button v-for="star in 5" :key="star" type="button" @mouseenter="hoverRating = star"
              @mouseleave="hoverRating = 0" @click="form.rating = star"
              class="text-4xl transition-all focus:outline-none cursor-pointer"
              :class="star <= (hoverRating || form.rating) ? 'text-amber-400' : 'text-outline-variant'">
              <span class="material-symbols-outlined"
                :style="star <= (hoverRating || form.rating) ? 'font-variation-settings: \'FILL\' 1;' : ''">star</span>
            </button>
          </div>
          <span class="text-xs font-bold" :class="ratingColor">{{ ratingText }}</span>
        </div>

        <!-- Comment Textarea -->
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-on-surface">Nhận xét</label>
          <textarea v-model="form.comment" rows="3" maxlength="500"
            placeholder="Chia sẻ trải nghiệm của bạn về người bán này..."
            class="w-full bg-surface-container border border-outline-variant text-on-surface text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"></textarea>
          <div class="text-right text-[10px] text-on-surface-variant">
            {{ form.comment.length }}/500
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-outline-variant bg-surface-container-low flex justify-end gap-3">
        <button @click="close" type="button"
          class="px-4 py-2 text-sm font-bold text-on-surface-variant hover:bg-surface-container rounded-full transition-colors cursor-pointer">
          Hủy
        </button>
        <button @click="submit" :disabled="form.rating === 0 || loading" type="button"
          class="px-6 py-2 text-sm font-bold bg-primary text-on-primary hover:bg-primary-container rounded-full shadow-sm transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
          <span v-if="loading" class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
          {{ isEdit ? 'Cập nhật' : 'Gửi đánh giá' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  isOpen: Boolean,
  transactionId: [Number, String],
  sellerId: [Number, String],
  existingReview: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'success']);

const loading = ref(false);
const hoverRating = ref(0);
const form = ref({
  rating: 0,
  comment: ''
});

const isEdit = computed(() => !!props.existingReview);

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    if (props.existingReview) {
      form.value.rating = props.existingReview.rating;
      form.value.comment = props.existingReview.comment || '';
    } else {
      form.value.rating = 0;
      form.value.comment = '';
    }
  }
});

const ratingText = computed(() => {
  const score = hoverRating.value || form.value.rating;
  switch (score) {
    case 1: return 'Rất tệ';
    case 2: return 'Tệ';
    case 3: return 'Bình thường';
    case 4: return 'Tốt';
    case 5: return 'Tuyệt vời!';
    default: return 'Chưa đánh giá';
  }
});

const ratingColor = computed(() => {
  const score = hoverRating.value || form.value.rating;
  if (score === 0) return 'text-on-surface-variant';
  if (score <= 2) return 'text-error';
  if (score === 3) return 'text-amber-500';
  return 'text-green-500';
});

const close = () => {
  emit('close');
};

const submit = async () => {
  if (form.value.rating === 0) return;

  loading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/api/reviews/${props.existingReview.id}`, {
        rating: form.value.rating,
        comment: form.value.comment
      });
    } else {
      response = await axios.post(`/api/users/${props.sellerId}/reviews`, {
        transaction_id: props.transactionId,
        rating: form.value.rating,
        comment: form.value.comment
      });
    }

    if (response.data.success) {
      alert(isEdit.value ? 'Đã cập nhật đánh giá thành công!' : 'Đã gửi đánh giá thành công!');
      emit('success', response.data.data);
      close();
    }
  } catch (error) {
    console.error('Lỗi khi gửi đánh giá:', error);
    alert(error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.');
  } finally {
    loading.value = false;
  }
};
</script>
