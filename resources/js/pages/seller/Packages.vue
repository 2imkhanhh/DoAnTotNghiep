<template>
  <SellerLayout title="Gói dịch vụ">
    <div class="packages-page max-w-7xl mx-auto space-y-8">

      <!-- Thông tin User -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">account_circle</span>
          Thông tin
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500 font-medium">Trạng thái VIP</p>
              <p class="text-lg font-bold" :class="authStore.user?.is_vip ? 'text-amber-500' : 'text-slate-700'">
                {{ authStore.user?.is_vip ? 'Đang kích hoạt' : 'Chưa đăng ký' }}
              </p>
              <p class="text-xs text-slate-500 mt-1" v-if="authStore.user?.is_vip && authStore.user?.vip_expires_at">
                Hạn đến: {{ formatDate(authStore.user?.vip_expires_at) }}
              </p>
            </div>
            <span class="material-symbols-outlined text-4xl"
              :class="authStore.user?.is_vip ? 'text-amber-500' : 'text-slate-300'">verified</span>
          </div>

          <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500 font-medium">Lượt đăng tin còn lại</p>

              <template v-if="authStore.user?.is_vip">
                <p class="text-xl font-bold text-amber-500 flex items-center gap-1">
                  Không giới hạn
                  <span class="material-symbols-outlined text-[18px]">all_inclusive</span>
                </p>
              </template>

              <template v-else>
                <p class="text-xl font-bold text-blue-600">
                  {{ authStore.user?.available_post_quota || 0 }} <span
                    class="text-sm font-medium text-slate-600">lượt</span>
                </p>
                <p class="text-xs text-slate-500 mt-1">Sử dụng để đăng tin. Bạn có thể mua thêm khi hết</p>
              </template>
            </div>
            <span class="material-symbols-outlined text-4xl"
              :class="authStore.user?.is_vip ? 'text-amber-300' : 'text-blue-300'">post_add</span>
          </div>
        </div>
      </div>

      <!-- Danh sách gói -->
      <div>
        <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">storefront</span>
          Các gói dịch vụ
        </h2>

        <div v-if="loadingPackages" class="flex justify-center py-10">
          <LoadingState />
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="pkg in packages" :key="pkg.id"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-all flex flex-col group relative">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r"
              :class="pkg.type === 'vip' ? 'from-amber-400 to-amber-600' : 'from-blue-400 to-blue-600'"></div>
            <div class="p-6 text-center border-b border-slate-100 bg-slate-50/50">
              <span class="material-symbols-outlined text-4xl mb-2"
                :class="pkg.type === 'vip' ? 'text-amber-500' : 'text-blue-500'">
                {{ pkg.type === 'vip' ? 'workspace_premium' : 'note_add' }}
              </span>
              <h3 class="text-lg font-bold text-slate-800">{{ pkg.name }}</h3>
              <p class="text-2xl font-black mt-2" :class="pkg.type === 'vip' ? 'text-amber-600' : 'text-blue-600'">{{
                formatPrice(pkg.price) }}đ</p>
            </div>
            <div class="p-6 flex-1 flex flex-col justify-between">
              <ul class="space-y-3 mb-6">
                <li class="flex items-start gap-2 text-sm text-slate-600">
                  <span class="material-symbols-outlined text-green-500 text-[18px]">check_circle</span>
                  <span v-if="pkg.type === 'vip'">Đăng tin không giới hạn trong {{ pkg.duration_days }} ngày</span>
                  <span v-else>Thêm {{ pkg.post_quota }} lượt đăng tin vào tài khoản</span>
                </li>
                <li class="flex items-start gap-2 text-sm text-slate-600">
                  <span class="material-symbols-outlined text-green-500 text-[18px]">check_circle</span>
                  <span v-if="pkg.type === 'vip'">Huy hiệu "Tick Xanh" nổi bật</span>
                  <span v-else>Lượt đăng không bao giờ hết hạn</span>
                </li>
              </ul>

              <button @click="confirmBuy(pkg)" :disabled="getButtonState(pkg).disabled"
                :title="getButtonState(pkg).tooltip"
                class="w-full py-2.5 rounded-xl font-bold text-white transition-all disabled:opacity-50" :class="[
                  pkg.type === 'vip' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700',
                  getButtonState(pkg).disabled ? 'cursor-not-allowed' : 'cursor-pointer'
                ]">
                <span v-if="buyingId === pkg.id"
                  class="material-symbols-outlined animate-spin text-[20px] align-middle mr-1">refresh</span>
                {{ getButtonState(pkg).text }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Lịch sử mua hàng -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
          <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">history</span>
            Lịch sử mua gói
          </h2>
          <button @click="fetchPurchases(1)"
            class="flex items-center gap-1 px-4 py-2 text-sm font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">refresh</span> Làm mới
          </button>
        </div>

        <div v-if="loadingPurchases" class="flex justify-center py-10">
          <LoadingState />
        </div>

        <div v-else-if="purchases.length === 0" class="text-center py-12 text-slate-500">
          <span class="material-symbols-outlined text-5xl mb-3 opacity-30">receipt_long</span>
          <p>Bạn chưa mua gói dịch vụ nào.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 text-slate-600 text-sm border-b border-slate-200">
                <th class="p-4 font-semibold text-center">STT</th>
                <th class="p-4 font-semibold">Tên gói</th>
                <th class="p-4 font-semibold">Số tiền</th>
                <th class="p-4 font-semibold">Ngày mua</th>
                <th class="p-4 font-semibold">Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(pur, index) in purchases" :key="pur.id"
                class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                <td class="p-4 font-medium text-slate-800 text-center">{{ getIndex(index) }}</td>
                <td class="p-4 font-medium text-slate-800">{{ pur.package?.name }}</td>
                <td class="p-4 font-bold text-error">{{ formatPrice(pur.price_paid) }}đ</td>
                <td class="p-4 text-sm text-slate-600">{{ formatDateTime(pur.created_at) }}</td>
                <td class="p-4">
                  <span class="px-3 py-1 rounded-full text-xs font-bold" :class="getStatusClass(pur.status)">
                    {{ getStatusText(pur.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="pagination && pagination.last_page > 1"
            class="p-4 border-t border-slate-100 flex justify-center gap-2">
            <button v-for="page in pagination.last_page" :key="page" @click="fetchPurchases(page)"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors"
              :class="page === pagination.current_page ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
              {{ page }}
            </button>
          </div>
        </div>
      </div>

    </div>

    <!-- QR Code Modal -->
    <div v-if="showQRModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
      <div
        class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden relative z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="p-6 text-center">
          <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-primary text-3xl">qr_code_scanner</span>
          </div>
          <h3 class="text-xl font-bold text-on-surface mb-2">Thanh toán gói dịch vụ</h3>
          <p class="text-sm text-on-surface-variant mb-4">Vui lòng quét mã QR dưới đây để thanh toán.</p>

          <div
            class="mb-6 inline-flex items-center gap-2 bg-error-container text-error px-4 py-2 rounded-full font-bold shadow-sm">
            <span class="material-symbols-outlined text-sm">schedule</span>
            Thời gian còn lại: {{ formattedTime }}
          </div>

          <div class="bg-white p-4 rounded-xl border border-outline-variant inline-block mb-6 shadow-sm">
            <img :src="qrCodeUrl" alt="VietQR" class="w-48 h-48 mx-auto object-contain">
          </div>

          <div class="text-left bg-surface-container-low p-4 rounded-xl text-sm mb-6 border border-outline-variant">
            <div class="flex justify-between mb-2">
              <span class="text-on-surface-variant">Ngân hàng:</span>
              <span class="font-bold">{{ currentAdminBank?.bank_name }}</span>
            </div>
            <div class="flex justify-between mb-2">
              <span class="text-on-surface-variant">Số tài khoản:</span>
              <span class="font-bold">{{ currentAdminBank?.bank_account_no }}</span>
            </div>
            <div class="flex justify-between mb-2">
              <span class="text-on-surface-variant">Chủ tài khoản:</span>
              <span class="font-bold">{{ currentAdminBank?.bank_account_name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-on-surface-variant">Số tiền:</span>
              <span class="font-bold text-error">{{ formatPrice(currentPurchase?.price_paid) }}đ</span>
            </div>
          </div>

          <div class="flex gap-3">
            <button @click="cancelQR"
              class="flex-1 py-3 bg-surface-variant text-on-surface-variant font-bold rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer">
              Hủy
            </button>
            <button v-if="currentPurchase?.payment_method !== 'payos'" @click="confirmPayment"
              class="flex-1 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer flex justify-center items-center gap-2">
              Hoàn tất
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Method Modal -->
    <div v-if="showPaymentMethodModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showPaymentMethodModal = false"></div>
      <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-2xl max-w-md w-full overflow-hidden relative z-10 animate-in fade-in zoom-in-95 duration-200">
        <div class="p-6">
          <h3 class="text-xl font-bold text-on-surface mb-4">Chọn phương thức thanh toán</h3>
          <p class="text-sm text-on-surface-variant mb-6">Gói <span class="font-bold text-primary">{{ selectedPackage?.name }}</span> - Giá: <span class="font-bold text-error">{{ formatPrice(selectedPackage?.price) }}đ</span></p>
          
          <div class="space-y-3">
            <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-slate-50"
                  :class="selectedPaymentMethod === 'payos' ? 'border-primary bg-primary/5' : 'border-slate-200'">
              <input type="radio" name="payment_method" value="payos" v-model="selectedPaymentMethod" class="mt-1">
              <div>
                <p class="font-bold text-slate-800 flex items-center gap-2">
                  Thanh toán qua PayOS (VietQR)
                  <span class="px-2 py-0.5 text-[10px] font-bold bg-green-100 text-green-700 rounded-full uppercase tracking-wider">Tự động duyệt</span>
                </p>
                <p class="text-sm text-slate-500 mt-1">Quét mã QR và hệ thống sẽ tự động duyệt gói ngay lập tức.</p>
              </div>
            </label>
            
            <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-slate-50"
                  :class="selectedPaymentMethod === 'manual' ? 'border-primary bg-primary/5' : 'border-slate-200'">
              <input type="radio" name="payment_method" value="manual" v-model="selectedPaymentMethod" class="mt-1">
              <div>
                <p class="font-bold text-slate-800">Chuyển khoản thủ công</p>
                <p class="text-sm text-slate-500 mt-1">Chuyển khoản và chờ Admin kiểm tra, duyệt thủ công.</p>
              </div>
            </label>
          </div>

          <div class="flex gap-3 mt-8">
            <button @click="showPaymentMethodModal = false"
              class="flex-1 py-3 bg-surface-variant text-on-surface-variant font-bold rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer">
              Hủy
            </button>
            <button @click="proceedBuy"
              class="flex-1 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer">
              Tiếp tục
            </button>
          </div>
        </div>
      </div>
    </div>

  </SellerLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { toast, confirmDialog } from '../../utils/alert';
import SellerLayout from '../../components/seller/SellerLayout.vue';
import LoadingState from '../../components/common/LoadingState.vue';

const authStore = useAuthStore();
const packages = ref([]);
const purchases = ref([]);
const pagination = ref(null);

const loadingPackages = ref(true);
const loadingPurchases = ref(true);
const buyingId = ref(null);

const showQRModal = ref(false);
const showPaymentMethodModal = ref(false);
const showPayosIframeModal = ref(false);
const selectedPaymentMethod = ref('payos');
const selectedPackage = ref(null);

const qrCodeUrl = ref('');
const currentPurchase = ref(null);
const currentAdminBank = ref(null);

const timeLeft = ref(300);
const timerInterval = ref(null);

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0');
  const s = (timeLeft.value % 60).toString().padStart(2, '0');
  return `${m}:${s}`;
});

const startTimer = () => {
  timeLeft.value = 300;
  if (timerInterval.value) clearInterval(timerInterval.value);
  timerInterval.value = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--;
      
      // Cứ mỗi 3 giây sẽ tự động gọi API kiểm tra trạng thái đơn hàng (Polling)
      // Dành riêng cho PayOS (vì Webhook tự động cập nhật Database)
      if (timeLeft.value % 3 === 0 && currentPurchase.value?.payment_method === 'payos') {
        checkPaymentStatus();
      }
    } else {
      clearInterval(timerInterval.value);
      cancelQR();
    }
  }, 1000);
};

const checkPaymentStatus = async () => {
  if (!currentPurchase.value) return;
  try {
    const response = await axios.get('/api/user/purchases?page=1');
    if (response.data.success) {
      const purchasesList = response.data.data.data;
      const updated = purchasesList.find(p => p.id === currentPurchase.value.id);
      
      // Nếu Webhook đã cập nhật status thành 'active'
      if (updated && updated.status === 'active') {
        stopTimer();
        showQRModal.value = false;
        toast('Thanh toán tự động thành công! Gói dịch vụ đã được kích hoạt.', 'success');
        fetchPurchases(1);
        authStore.fetchUser(); // Cập nhật VIP
      }
    }
  } catch (e) {
    // Bỏ qua lỗi mạng chập chờn
  }
};

const stopTimer = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value);
    timerInterval.value = null;
  }
};

const cancelQR = async () => {
  stopTimer();
  if (!currentPurchase.value) {
    showQRModal.value = false;
    return;
  }

  try {
    await axios.delete(`/api/packages/purchases/${currentPurchase.value.id}/cancel`);
    toast('Đã huỷ thanh toán gói dịch vụ.', 'info');
  } catch (error) {
    console.error('Lỗi khi huỷ yêu cầu:', error);
  } finally {
    showQRModal.value = false;
    currentPurchase.value = null;
    fetchPurchases(1);
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN');
};

const formatDateTime = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleString('vi-VN');
};

const getButtonState = (pkg) => {
  const isPending = purchases.value.some(p => p.package_id === pkg.id && p.status === 'pending');

  if (isPending) {
    return { disabled: true, text: 'Đang chờ duyệt', tooltip: 'Bạn đang có yêu cầu chờ duyệt cho gói này' };
  }

  if (buyingId.value === pkg.id) {
    return { disabled: true, text: 'Đang xử lý...', tooltip: '' };
  }

  if (pkg.type === 'vip' && authStore.user?.is_vip) {
    return { disabled: false, text: 'Gia hạn VIP', tooltip: 'Thời gian sẽ được cộng dồn vào gói VIP hiện tại của bạn' };
  }

  if (pkg.type !== 'vip' && authStore.user?.is_vip) {
    return { disabled: true, text: 'Mua ngay', tooltip: 'Bạn đang là VIP (không giới hạn tin đăng) nên không cần mua thêm lượt' };
  }

  return { disabled: false, text: 'Mua ngay', tooltip: '' };
};

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-100 text-amber-700';
    case 'active': return 'bg-green-100 text-green-700';
    case 'rejected': return 'bg-red-100 text-red-700';
    default: return 'bg-slate-100 text-slate-700';
  }
};

const getStatusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ duyệt';
    case 'active': return 'Thành công';
    case 'rejected': return 'Bị từ chối';
    default: return status;
  }
};

const getIndex = (index) => {
  if (!pagination.value) return index + 1;
  return (pagination.value.current_page - 1) * pagination.value.per_page + index + 1;
};

const fetchPackages = async () => {
  loadingPackages.value = true;
  try {
    const response = await axios.get('/api/packages');
    if (response.data.success) {
      packages.value = response.data.data;
    }
  } catch (error) {
    console.error('Error fetching packages', error);
  } finally {
    loadingPackages.value = false;
  }
};

const fetchPurchases = async (page = 1) => {
  loadingPurchases.value = true;
  try {
    const response = await axios.get(`/api/user/purchases?page=${page}`);
    if (response.data.success) {
      purchases.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error('Error fetching purchases', error);
  } finally {
    loadingPurchases.value = false;
  }
};

const confirmBuy = (pkg) => {
  selectedPackage.value = pkg;
  showPaymentMethodModal.value = true;
};

const proceedBuy = () => {
  showPaymentMethodModal.value = false;
  buyPackage(selectedPackage.value.id, selectedPaymentMethod.value);
};

const buyPackage = async (packageId, paymentMethod) => {
  buyingId.value = packageId;
  try {
    const response = await axios.post('/api/packages/buy', { 
      package_id: packageId,
      payment_method: paymentMethod
    });
    
    if (response.data.success) {
      const purchase = response.data.data.purchase;
      currentPurchase.value = purchase;

      if (paymentMethod === 'payos' && response.data.data.payos_data) {
        // Lấy dữ liệu PayOS trả về để tạo mã VietQR
        const payosData = response.data.data.payos_data;
        const bin = payosData.bin;
        const accountNo = payosData.accountNumber;
        const amount = payosData.amount;
        const description = payosData.description;
        const accountName = payosData.accountName;

        qrCodeUrl.value = `https://img.vietqr.io/image/${bin}-${accountNo}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(description)}&accountName=${encodeURIComponent(accountName)}`;
        
        currentAdminBank.value = {
          bank_name: 'Ngân hàng thụ hưởng (PayOS)',
          bank_account_no: accountNo,
          bank_account_name: accountName
        };

        showQRModal.value = true;
        startTimer();
        fetchPurchases(1);
        return;
      }

      // --- Luồng Manual ---
      toast('Tạo yêu cầu mua thành công!', 'success');
      const adminBank = response.data.data.admin_bank;
      const amount = purchase.price_paid;
      const orderInfo = `MUA GOI ${purchase.id}`;
      const bankId = adminBank.bank_name.split(' ')[0] || 'MB';

      qrCodeUrl.value = `https://img.vietqr.io/image/${bankId}-${adminBank.bank_account_no}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(orderInfo)}&accountName=${encodeURIComponent(adminBank.bank_account_name)}`;
      
      currentAdminBank.value = adminBank;
      showQRModal.value = true;
      startTimer();
      fetchPurchases(1);
    }
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  } finally {
    buyingId.value = null;
  }
};

const confirmPayment = async () => {
  stopTimer();
  showQRModal.value = false;
  
  if (currentPurchase.value?.payment_method !== 'payos') {
    toast('Đã xác nhận thanh toán. Vui lòng chờ Admin duyệt!', 'success');
    fetchPurchases(1);
  }
};

onMounted(() => {
  fetchPackages();
  fetchPurchases();
});

onUnmounted(() => {
  stopTimer();
});
</script>
