<template>
  <div class="checkout-page bg-slate-50 min-h-screen py-10">
    <div v-if="loading" class="flex flex-col items-center justify-center min-h-[50vh]">
      <span class="material-symbols-outlined text-5xl animate-spin mb-4 text-primary">progress_activity</span>
      <p class="font-bold text-lg text-slate-700">Đang tải thông tin...</p>
    </div>

    <div v-else-if="post" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 mb-8">
        <button @click="$router.back()" class="p-2 hover:bg-slate-200 rounded-full transition-colors">
          <span class="material-symbols-outlined">arrow_back</span>
        </button>
        <h1 class="text-2xl font-bold text-on-surface">Thanh toán & Đặt hàng</h1>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Form thông tin giao hàng -->
        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-outline-variant p-6 lg:p-8">
          <h2 class="text-xl font-bold mb-6 text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">local_shipping</span>
            Thông tin nhận hàng
          </h2>

          <form @submit.prevent="submitOrder" class="space-y-6">

            <label class="checkbox-label-custom">
              <input type="checkbox" v-model="useMyInfo" @change="toggleMyInfo">
              <span>Sử dụng thông tin cá nhân của tôi</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- Name -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Họ và tên người nhận</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">person</span>
                  <input v-model="form.shipping_name" type="text" required
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="Nhập họ tên người nhận">
                </div>
              </div>

              <!-- Phone -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Số điện thoại</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">call</span>
                  <input v-model="form.shipping_phone" type="tel" required
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="Nhập số điện thoại">
                </div>
              </div>

              <!-- Administrative Units -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Tỉnh / Thành phố</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">map</span>
                  <select v-model="form.shipping_province_id" @change="onProvinceChange" required
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                    <option value="">Chọn Tỉnh/Thành</option>
                    <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
                  </select>
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Phường / Xã</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">location_city</span>
                  <select v-model="form.shipping_ward_id" :disabled="!form.shipping_province_id" required
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none disabled:opacity-50">
                    <option value="">Chọn Phường/Xã</option>
                    <option v-for="w in wards" :key="w.code" :value="w.code">{{ w.name }}</option>
                  </select>
                </div>
              </div>

              <!-- Specific Address -->
              <div class="space-y-2 sm:col-span-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Địa chỉ cụ thể</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">home</span>
                  <input v-model="form.shipping_address" type="text" required
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="Số nhà, tên ngõ, đường...">
                </div>
              </div>

              <!-- Note -->
              <div class="space-y-2 sm:col-span-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Ghi chú cho người bán (Tùy chọn)</label>
                <textarea v-model="form.shipping_note" rows="3"
                  class="w-full bg-surface-container border border-outline-variant rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                  placeholder="Ví dụ: Giao hàng giờ hành chính..."></textarea>
              </div>
            </div>

            <!-- Submit button on Mobile -->
            <div class="lg:hidden mt-8 pt-6 border-t border-outline-variant">
              <button type="submit" :disabled="submitting"
                class="w-full py-4 bg-primary text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 flex justify-center items-center gap-2 cursor-pointer">
                <span v-if="submitting"
                  class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                Xác nhận đặt hàng
              </button>
            </div>
          </form>
        </div>

        <!-- Tóm tắt đơn hàng -->
        <div class="w-full lg:w-[400px] shrink-0 space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-outline-variant p-6 sticky top-24">
            <h3 class="font-bold text-lg mb-4 text-on-surface">Thông tin đơn hàng</h3>

            <div class="flex gap-4 pb-4 border-b border-outline-variant mb-4">
              <img :src="post.images?.[0]?.image_path || 'https://via.placeholder.com/150'"
                class="w-20 h-20 rounded-xl object-cover border border-outline-variant">
              <div class="flex-1">
                <h4 class="font-bold text-sm text-on-surface line-clamp-2 leading-snug">{{ post.title }}</h4>
                <div class="mt-2 text-error font-extrabold text-lg">{{ formatPrice(post.price) }}đ</div>
              </div>
            </div>

            <div class="space-y-3 text-sm">
              <div class="flex justify-between text-on-surface-variant">
                <span>Tạm tính</span>
                <span class="font-medium text-on-surface">{{ formatPrice(post.price) }}đ</span>
              </div>
              <div class="flex justify-between text-on-surface-variant">
                <span>Phí vận chuyển</span>
                <span class="font-medium text-on-surface">Thỏa thuận</span>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-outline-variant">
              <div class="flex justify-between items-center mb-6">
                <span class="font-bold text-on-surface">Tổng tiền</span>
                <span class="font-black text-2xl text-error">{{ formatPrice(post.price) }}đ</span>
              </div>

              <!-- Submit button on Desktop -->
              <button @click="submitOrder" :disabled="submitting"
                class="hidden lg:flex w-full py-4 bg-primary text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 justify-center items-center gap-2 cursor-pointer">
                <span v-if="submitting"
                  class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                Xác nhận Đặt hàng
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const loading = ref(true);
const submitting = ref(false);
const useMyInfo = ref(false);
const post = ref(null);
const provinces = ref([]);
const wards = ref([]);

const form = ref({
  shipping_name: '',
  shipping_phone: '',
  shipping_address: '',
  shipping_province_id: '',
  shipping_ward_id: '',
  shipping_note: ''
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const toggleMyInfo = async () => {
  if (useMyInfo.value && authStore.user) {
    form.value.shipping_name = authStore.user.name || '';
    form.value.shipping_phone = authStore.user.phone || '';
    form.value.shipping_address = authStore.user.address || '';
    form.value.shipping_province_id = authStore.user.province_id || '';
    if (form.value.shipping_province_id) {
      await fetchInitialWards(form.value.shipping_province_id);
    }
    form.value.shipping_ward_id = authStore.user.ward_id || '';
  } else {
    form.value.shipping_name = '';
    form.value.shipping_phone = '';
    form.value.shipping_address = '';
    form.value.shipping_province_id = '';
    form.value.shipping_ward_id = '';
    wards.value = [];
  }
};

const fetchProvinces = async () => {
  try {
    const res = await axios.get('/api/locations/provinces');
    provinces.value = res.data;
  } catch (error) {
    console.error('Failed to fetch provinces:', error);
  }
};

const onProvinceChange = async () => {
  form.value.shipping_ward_id = '';
  wards.value = [];
  if (!form.value.shipping_province_id) return;

  try {
    const res = await axios.get(`/api/locations/wards/${form.value.shipping_province_id}`);
    wards.value = res.data;
  } catch (error) {
    console.error('Failed to fetch wards:', error);
  }
};

const fetchInitialWards = async (provinceId) => {
  try {
    const res = await axios.get(`/api/locations/wards/${provinceId}`);
    wards.value = res.data;
  } catch (error) {
    console.error('Failed to fetch initial wards:', error);
  }
};

const fetchPostDetail = async () => {
  loading.value = true;
  try {
    const slug = route.params.slug;
    const response = await axios.get(`/api/posts/${slug}`);
    post.value = response.data.data;

    // Check validity
    if (post.value.user_id === authStore.user?.id) {
      alert('Bạn không thể mua sản phẩm của chính mình.');
      router.push('/');
      return;
    }

    if (post.value.status !== 'active') {
      alert('Sản phẩm này hiện không thể mua.');
      router.push('/');
      return;
    }

    if (post.value.is_ordered) {
      alert('Bạn đã đặt mua sản phẩm này rồi, vui lòng chờ người bán xử lý.');
      router.push('/my-orders');
      return;
    }

    // Bỏ tự động điền thông tin (người dùng sẽ tự tick nếu muốn)
  } catch (error) {
    console.error('Lỗi khi tải thông tin:', error);
    alert('Không thể tải thông tin sản phẩm.');
    router.push('/');
  } finally {
    loading.value = false;
  }
};

const submitOrder = async () => {
  if (!form.value.shipping_name || !form.value.shipping_phone || !form.value.shipping_address || !form.value.shipping_province_id || !form.value.shipping_ward_id) {
    alert('Vui lòng điền đầy đủ thông tin giao hàng bắt buộc.');
    return;
  }

  const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/g;
  if (!phoneRegex.test(form.value.shipping_phone)) {
    alert('Số điện thoại không hợp lệ.');
    return;
  }

  submitting.value = true;
  try {
    const response = await axios.post('/api/orders/checkout', {
      post_id: post.value.id,
      ...form.value
    });

    if (response.data.success) {
      alert('Đặt hàng thành công!');
      router.push('/my-orders');
    }
  } catch (error) {
    console.error('Lỗi khi đặt hàng:', error);
    alert(error.response?.data?.message || 'Có lỗi xảy ra khi đặt hàng.');
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  if (!authStore.isLoggedIn) {
    router.push('/login');
    return;
  }
  fetchProvinces();
  fetchPostDetail();
});
</script>

<style scoped>
.checkbox-label-custom {
  display: block;
  cursor: pointer;
  user-select: none;
  font-weight: 700;
  font-size: 0.9rem;
  line-height: 1.5;
  color: var(--on-surface, #1e293b);
}

.checkbox-label-custom input[type="checkbox"] {
  width: 16px;
  height: 16px;
  margin: 0 0.5rem 0 0 !important;
  cursor: pointer;
  vertical-align: -2px !important;
  appearance: checkbox !important;
  -webkit-appearance: checkbox !important;
  padding: 0 !important;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
}
</style>
