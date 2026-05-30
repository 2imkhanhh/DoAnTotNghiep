<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Sidebar -->
      <aside class="w-full md:w-80 shrink-0">
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm sticky top-24">
          <div class="p-6 border-b border-outline-variant">
            <div class="flex items-center gap-4">
              <div class="relative group">
                <img
                  :src="profileData.avatar || 'https://ui-avatars.com/api/?name=' + (profileData.name || 'User') + '&background=020037&color=fff'"
                  alt="Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-primary-fixed">
                <div @click="$refs.fileInput.click()"
                  class="absolute inset-0 bg-black/20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                  <span class="material-symbols-outlined text-white text-sm">edit</span>
                </div>
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileUpload">
              </div>
              <div class="overflow-hidden">
                <h2 class="font-bold text-on-surface truncate">{{ profileData.name || 'Người dùng' }}</h2>
                <p class="text-xs text-on-surface-variant truncate">{{ profileData.email }}</p>
              </div>
            </div>
            <!-- Followers and Following Display -->
            <div class="flex justify-center gap-12 mt-6 mb-2">
              <button @click="openFollowModal('followers')" type="button"
                class="text-center cursor-pointer focus:outline-none group">
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{
                  profileData.followers_count || 0 }}</div>
                <div
                  class="text-[12px] font-medium text-on-surface-variant group-hover:text-primary transition-colors mt-0.5">
                  Người theo dõi</div>
              </button>

              <button @click="openFollowModal('following')" type="button"
                class="text-center cursor-pointer focus:outline-none group">
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{
                  profileData.followings_count || 0 }}</div>
                <div
                  class="text-[12px] font-medium text-on-surface-variant group-hover:text-primary transition-colors mt-0.5">
                  Đang theo dõi</div>
              </button>
            </div>
          </div>
          <nav class="p-2">
            <button @click="activeTab = 'info'"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 cursor-pointer',
                activeTab === 'info' ? 'bg-primary text-on-primary font-bold shadow-md' : 'text-on-surface hover:bg-surface-container-low']">
              <span class="material-symbols-outlined">person</span>
              <span>Thông tin cá nhân</span>
            </button>

            <button @click="activeTab = 'password'"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 mt-1 cursor-pointer',
                activeTab === 'password' ? 'bg-primary text-on-primary font-bold shadow-md' : 'text-on-surface hover:bg-surface-container-low']">
              <span class="material-symbols-outlined">lock</span>
              <span>Đổi mật khẩu</span>
            </button>
            <button @click="activeTab = 'reviews'"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 mt-1 cursor-pointer',
                activeTab === 'reviews' ? 'bg-primary text-on-primary font-bold shadow-md' : 'text-on-surface hover:bg-surface-container-low']">
              <span class="material-symbols-outlined">star</span>
              <span>Đánh giá của tôi</span>
            </button>
            <div class="border-t border-outline-variant my-2"></div>
            <button @click="authStore.logout()"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-error hover:bg-error-container transition-all duration-200 cursor-pointer">
              <span class="material-symbols-outlined font-bold">logout</span>
              <span class="font-bold">Đăng xuất</span>
            </button>
          </nav>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="grow max-w-3xl w-full">
        <!-- Info Tab -->
        <div v-if="activeTab === 'info'"
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="p-6 sm:p-8 border-b border-outline-variant">
            <h1 class="text-2xl font-bold text-on-surface">Thông tin cá nhân</h1>
            <p class="text-on-surface-variant">Quản lý thông tin hồ sơ của bạn để bảo mật tài khoản</p>
          </div>

          <div class="p-6 sm:p-8">
            <form @submit.prevent="updateProfile" class="space-y-6 max-w-2xl">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="space-y-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Họ và tên</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">person</span>
                    <input v-model="profileData.name" type="text"
                      class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      placeholder="Nhập họ tên của bạn">
                  </div>
                  <p v-if="errors.name" class="text-xs text-error mt-1 px-1">{{ errors.name[0] }}</p>
                </div>

                <!-- Email (Read-only) -->
                <div class="space-y-2 opacity-70">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Email</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">mail</span>
                    <input :value="profileData.email" type="email" readonly
                      class="w-full bg-surface-container-low border border-outline-variant rounded-xl pl-10 pr-4 py-3 cursor-not-allowed"
                      placeholder="email@example.com">
                  </div>
                  <p class="text-[10px] text-on-surface-variant mt-1 px-1">Email không thể thay đổi</p>
                </div>

                <!-- Administrative Units -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:col-span-2">
                  <div class="space-y-2">
                    <label class="text-sm font-bold text-on-surface-variant px-1">Tỉnh / Thành phố</label>
                    <div class="relative">
                      <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">map</span>
                      <select v-model="profileData.province_id" @change="onProvinceChange"
                        class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                        <option value="">Chọn Tỉnh/Thành</option>
                        <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
                      </select>
                    </div>
                    <p v-if="errors.province_id" class="text-xs text-error mt-1 px-1">{{ errors.province_id[0] }}</p>
                  </div>

                  <div class="space-y-2">
                    <label class="text-sm font-bold text-on-surface-variant px-1">Phường / Xã</label>
                    <div class="relative">
                      <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">location_city</span>
                      <select v-model="profileData.ward_id" @change="onWardChange" :disabled="!profileData.province_id"
                        class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none disabled:opacity-50">
                        <option value="">Chọn Phường/Xã</option>
                        <option v-for="w in wards" :key="w.code" :value="w.code">{{ w.name }}</option>
                      </select>
                    </div>
                    <p v-if="errors.ward_id" class="text-xs text-error mt-1 px-1">{{ errors.ward_id[0] }}</p>
                  </div>
                </div>

                <!-- Phone -->
                <div class="space-y-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Số điện thoại</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">call</span>
                    <input v-model="profileData.phone" type="tel"
                      class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      placeholder="Nhập số điện thoại">
                  </div>
                  <p v-if="errors.phone" class="text-xs text-error mt-1 px-1">{{ errors.phone[0] }}</p>
                </div>

                <!-- Specific Address -->
                <div class="space-y-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Địa chỉ cụ thể</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">home</span>
                    <input v-model="profileData.address" type="text"
                      class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      placeholder="Số nhà, tên ngõ, đường...">
                  </div>
                  <p v-if="errors.address" class="text-xs text-error mt-1 px-1">{{ errors.address[0] }}</p>
                </div>
                <!-- Avatar (File Upload) -->
                <div class="space-y-2 sm:col-span-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Ảnh đại diện</label>
                  <div
                    class="flex items-center gap-4 p-4 bg-surface-container border border-outline-variant rounded-xl">
                    <img
                      :src="profileData.avatar || 'https://ui-avatars.com/api/?name=' + (profileData.name || 'User') + '&background=020037&color=fff'"
                      class="w-16 h-16 rounded-full object-cover border-2 border-primary-fixed">
                    <div class="grow">
                      <p class="text-xs text-on-surface-variant mb-2">Dung lượng file tối đa 2MB. Định dạng: .JPEG, .PNG
                      </p>
                      <button type="button" @click="$refs.fileInput.click()"
                        class="px-4 py-2 bg-surface-container-high text-on-surface text-sm font-bold rounded-lg border border-outline-variant hover:bg-surface-dim transition-all cursor-pointer">
                        Chọn ảnh mới
                      </button>
                      <input type="file" ref="fileInput" @change="handleFileUpload" accept="image/jpeg, image/png"
                        hidden />
                    </div>
                  </div>
                  <p v-if="errors.avatar" class="text-xs text-error mt-1 px-1">{{ errors.avatar[0] }}</p>
                </div>
              </div>

              <div class="pt-4 border-t border-outline-variant flex justify-end">
                <button type="submit" :disabled="loading"
                  class="px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:shadow-primary/20 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 flex items-center gap-2 cursor-pointer">
                  <span v-if="loading"
                    class="w-4 h-4 border-2 border-on-primary border-t-transparent rounded-full animate-spin"></span>
                  Lưu thay đổi
                </button>
              </div>
            </form>
          </div>
        </div>


        <!-- Password Tab -->
        <div v-if="activeTab === 'password'"
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="p-6 sm:p-8 border-b border-outline-variant">
            <h1 class="text-2xl font-bold text-on-surface">Đổi mật khẩu</h1>
            <p class="text-on-surface-variant">Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu với người khác</p>
          </div>

          <div class="p-6 sm:p-8">
            <form @submit.prevent="changePassword" class="space-y-6 max-w-md">
              <!-- Current Password -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Mật khẩu hiện tại</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">lock</span>
                  <input v-model="passwordData.current_password" type="password"
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="••••••••">
                </div>
                <p v-if="passwordErrors.current_password" class="text-xs text-error mt-1 px-1">{{
                  passwordErrors.current_password[0] }}</p>
              </div>

              <!-- New Password -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Mật khẩu mới</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">lock_reset</span>
                  <input v-model="passwordData.new_password" type="password"
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="••••••••">
                </div>
                <p v-if="passwordErrors.new_password" class="text-xs text-error mt-1 px-1">{{
                  passwordErrors.new_password[0] }}</p>
              </div>

              <!-- Confirm Password -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Xác nhận mật khẩu mới</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">verified_user</span>
                  <input v-model="passwordData.new_password_confirmation" type="password"
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="••••••••">
                </div>
              </div>

              <div class="pt-4 flex justify-end">
                <button type="submit" :disabled="passwordLoading"
                  class="w-full sm:w-auto px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:shadow-primary/20 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 flex items-center justify-center gap-2 cursor-pointer">
                  <span v-if="passwordLoading"
                    class="w-4 h-4 border-2 border-on-primary border-t-transparent rounded-full animate-spin"></span>
                  Đổi mật khẩu
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Reviews Tab -->
        <div v-if="activeTab === 'reviews'"
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="p-6 sm:p-8 border-b border-outline-variant">
            <h1 class="text-2xl font-bold text-on-surface">Đánh giá của tôi</h1>
            <p class="text-on-surface-variant">Xem các đánh giá, phản hồi và xếp hạng từ người dùng đã giao dịch với bạn
            </p>
          </div>

          <!-- Summary Score Card -->
          <div class="p-6 sm:p-8 border-b border-outline-variant bg-surface-container-low/20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <!-- Average Score -->
              <div class="text-center md:border-r border-outline-variant py-4">
                <div class="text-5xl font-black text-on-surface mb-2">{{ reviewStats.avg }}</div>
                <div class="flex justify-center gap-1 mb-2">
                  <span v-for="star in 5" :key="star" class="material-symbols-outlined text-sm"
                    :class="star <= Math.round(reviewStats.avg) ? 'text-amber-500' : 'text-outline-variant'"
                    :style="star <= Math.round(reviewStats.avg) ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">
                    star </span>
                </div>
                <p class="text-xs text-on-surface-variant">Trung bình trên {{ reviewStats.total }} đánh giá</p>
              </div>

              <!-- Stars Breakdown -->
              <div class="col-span-2 space-y-2 md:pl-6">
                <!-- 5 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">5 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full"
                      :style="{ width: (reviewStats.total ? (reviewStats.counts[5] / reviewStats.total * 100) : 0) + '%' }">
                    </div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">{{ reviewStats.total ?
                    Math.round(reviewStats.counts[5] / reviewStats.total * 100) : 0 }}%</span>
                </div>
                <!-- 4 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">4 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full"
                      :style="{ width: (reviewStats.total ? (reviewStats.counts[4] / reviewStats.total * 100) : 0) + '%' }">
                    </div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">{{ reviewStats.total ?
                    Math.round(reviewStats.counts[4] / reviewStats.total * 100) : 0 }}%</span>
                </div>
                <!-- 3 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">3 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full"
                      :style="{ width: (reviewStats.total ? (reviewStats.counts[3] / reviewStats.total * 100) : 0) + '%' }">
                    </div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">{{ reviewStats.total ?
                    Math.round(reviewStats.counts[3] / reviewStats.total * 100) : 0 }}%</span>
                </div>
                <!-- 2 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">2 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full"
                      :style="{ width: (reviewStats.total ? (reviewStats.counts[2] / reviewStats.total * 100) : 0) + '%' }">
                    </div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">{{ reviewStats.total ?
                    Math.round(reviewStats.counts[2] / reviewStats.total * 100) : 0 }}%</span>
                </div>
                <!-- 1 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">1 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full"
                      :style="{ width: (reviewStats.total ? (reviewStats.counts[1] / reviewStats.total * 100) : 0) + '%' }">
                    </div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">{{ reviewStats.total ?
                    Math.round(reviewStats.counts[1] / reviewStats.total * 100) : 0 }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Filter Options -->
          <div class="px-6 sm:px-8 py-4 border-b border-outline-variant flex flex-wrap gap-2">
            <button v-for="filter in dynamicRatingFilters" :key="filter.value"
              @click="currentRatingFilter = filter.value"
              :class="['px-4 py-2 rounded-full text-xs font-bold transition-all cursor-pointer border',
                currentRatingFilter === filter.value
                  ? 'bg-primary text-on-primary border-primary shadow-sm'
                  : 'bg-surface-container-low text-on-surface-variant border-outline-variant hover:bg-surface-container']">
              {{ filter.label }}
            </button>
          </div>

          <!-- Review List -->
          <div class="p-6 sm:p-8 space-y-6">
            <div v-if="filteredReviews.length === 0" class="text-center py-12 text-on-surface-variant">
              <span class="material-symbols-outlined text-4xl mb-2 opacity-40">rate_review</span>
              <p class="font-medium">Chưa có đánh giá.</p>
            </div>

            <div v-else v-for="rev in filteredReviews" :key="rev.id"
              class="border-b border-outline-variant last:border-0 pb-6 last:pb-0">
              <div class="flex items-start gap-4">
                <img
                  :src="rev.reviewer?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(rev.reviewer?.name) + '&background=random'"
                  class="w-10 h-10 rounded-full object-cover shrink-0 border border-outline-variant" />
                <div class="flex-1">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                    <h4 class="font-bold text-on-surface text-sm sm:text-base">{{ rev.reviewer?.name }}</h4>
                    <span class="text-xs text-on-surface-variant">{{ formatTime(rev.created_at) }}</span>
                  </div>

                  <div class="flex items-center gap-1 mb-2">
                    <span v-for="star in 5" :key="star" class="material-symbols-outlined text-sm"
                      :class="star <= rev.rating ? 'text-amber-500' : 'text-outline-variant'"
                      :style="star <= rev.rating ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">
                      star </span>
                    <span v-if="rev.order && rev.order.post"
                      class="text-xs text-on-surface-variant ml-2 font-medium">Mua hàng: <router-link
                        :to="`/post/${rev.order.post.slug}`"
                        class="text-primary hover:underline cursor-pointer font-bold">{{ rev.order.post.title
                        }}</router-link></span>
                  </div>

                  <p class="text-on-surface text-sm leading-relaxed mb-3 mt-2">
                    {{ rev.comment }}
                  </p>

                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Reply Modal Overlay -->
    <div v-if="replyModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeReplyModal"></div>
      <div
        class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200 relative z-10">
        <div class="p-6 border-b border-outline-variant flex justify-between items-center">
          <h3 class="text-lg font-bold text-on-surface">Phản hồi đánh giá</h3>
          <button @click="closeReplyModal" class="text-on-surface-variant hover:text-on-surface">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="bg-surface-container p-3 rounded-xl border border-outline-variant">
            <div class="font-bold text-on-surface text-xs mb-1">{{ replyModal.review?.reviewer_name }} ({{
              replyModal.review?.rating }} ★)</div>
            <p class="text-on-surface-variant text-xs italic">"{{ replyModal.review?.comment }}"</p>
          </div>
          <div class="space-y-2">
            <label class="text-xs font-bold text-on-surface-variant px-1">Nội dung phản hồi</label>
            <textarea v-model="replyModal.text" rows="3"
              class="w-full bg-surface-container border border-outline-variant rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
              placeholder="Nhập nội dung phản hồi của bạn..."></textarea>
          </div>
        </div>
        <div class="p-6 border-t border-outline-variant flex justify-end gap-3">
          <button @click="closeReplyModal"
            class="px-4 py-2 border border-outline-variant text-on-surface-variant text-sm font-bold rounded-xl hover:bg-surface-container-low transition-all cursor-pointer">
            Hủy
          </button>
          <button @click="submitReply" :disabled="!replyModal.text.trim()"
            class="px-5 py-2 bg-primary text-on-primary text-sm font-bold rounded-xl shadow-md hover:shadow-primary/20 transition-all cursor-pointer disabled:opacity-50">
            Gửi phản hồi
          </button>
        </div>
      </div>
    </div>

    <!-- Follow Modal Overlay -->
    <div v-if="followModal.show"
      class="fixed inset-0 z-100 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
      @click.self="closeFollowModal">
      <div
        class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col max-h-[80vh] animate-fadeIn">
        <div
          class="px-5 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
          <h3 class="font-extrabold text-lg text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">
              {{ followModal.type === 'followers' ? 'group' : 'person_add' }}
            </span>
            {{ followModal.type === 'followers' ? 'Người theo dõi' : 'Đang theo dõi' }} ({{ followModal.list.length }})
          </h3>
          <button @click="closeFollowModal"
            class="text-on-surface-variant hover:text-error transition-colors p-1 rounded-full focus:outline-none flex items-center justify-center cursor-pointer">
            <span class="material-symbols-outlined block">close</span>
          </button>
        </div>
        <div class="p-3 overflow-y-auto grow custom-scrollbar">
          <div v-if="followModal.isLoading"
            class="text-center py-10 text-on-surface-variant flex flex-col items-center justify-center">
            <span class="material-symbols-outlined animate-spin text-4xl mb-2 text-primary">progress_activity</span>
            <p class="font-medium text-sm">Đang tải dữ liệu...</p>
          </div>
          <div v-else-if="followModal.list.length === 0" class="text-center py-10 text-on-surface-variant">
            Danh sách này hiện đang trống.
          </div>
          <div v-else class="space-y-2">
            <div v-for="user in followModal.list" :key="user.id"
              class="flex items-center justify-between p-3 rounded-xl hover:bg-surface-container transition-colors border border-transparent hover:border-outline-variant cursor-pointer">
              <div class="flex items-center gap-3">
                <img :src="user.avatar || 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'"
                  class="w-12 h-12 rounded-full object-cover border border-outline-variant shrink-0" />
                <div class="min-w-0">
                  <div class="font-bold text-on-surface text-sm truncate w-24 sm:w-32" :title="user.name">{{ user.name
                  }}</div>
                </div>
              </div>
              <button v-if="followModal.type === 'following'" @click="unfollowUser(user.id)" type="button"
                class="px-2.5 py-1.5 text-xs font-bold text-error bg-error-container hover:bg-error-container/80 rounded-lg transition-all cursor-pointer focus:outline-none shrink-0">
                Hủy theo dõi
              </button>
              <button v-else-if="!user.isFollowing" @click="followBackUser(user.id)" type="button"
                class="px-2.5 py-1.5 text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 rounded-lg transition-all cursor-pointer focus:outline-none shrink-0">
                Theo dõi lại
              </button>
              <button v-else type="button"
                class="px-2.5 py-1.5 text-xs font-bold text-on-surface-variant bg-surface-container-high rounded-lg cursor-not-allowed shrink-0">
                Đang theo dõi
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const activeTab = ref('info');
const fileInput = ref(null);
const avatarFile = ref(null);
const loading = ref(false);
const passwordLoading = ref(false);
const errors = ref({});
const passwordErrors = ref({});

// Reviews and Rating Statistics System
const currentRatingFilter = ref('all');
const ratingFilters = [
  { label: 'Tất cả (156)', value: 'all' },
  { label: '5 Sao (132)', value: '5' },
  { label: '4 Sao (16)', value: '4' },
  { label: '3 Sao (5)', value: '3' },
  { label: '2 Sao (2)', value: '2' },
  { label: '1 Sao (1)', value: '1' }
];

const reviews = ref([]);
const reviewsPagination = ref(null);

const fetchReviews = async (page = 1) => {
  if (!authStore.user?.id) return;
  try {
    const response = await axios.get(`/api/users/${authStore.user.id}/reviews?page=${page}`);
    if (response.data.success) {
      if (page === 1) {
        reviews.value = response.data.data.data;
      } else {
        reviews.value = [...reviews.value, ...response.data.data.data];
      }
      reviewsPagination.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi khi tải đánh giá:', error);
  }
};

watch(() => authStore.user, (user) => {
  if (user) {
    fetchReviews();
  }
}, { immediate: true });



watch(activeTab, (tab) => {
  if (tab === 'reviews' && reviews.value.length === 0) {
    fetchReviews();
  }

});

const filteredReviews = computed(() => {
  if (currentRatingFilter.value === 'all') return reviews.value;
  return reviews.value.filter(rev => rev.rating === parseInt(currentRatingFilter.value));
});

const reviewStats = computed(() => {
  const total = reviews.value.length;
  if (total === 0) return { avg: 0, counts: { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 }, total: 0 };

  let sum = 0;
  const counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
  reviews.value.forEach(r => {
    sum += r.rating;
    counts[r.rating] = (counts[r.rating] || 0) + 1;
  });

  return {
    avg: (sum / total).toFixed(1),
    counts,
    total
  };
});

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN');
};

const dynamicRatingFilters = computed(() => {
  const stats = reviewStats.value;
  return [
    { label: `Tất cả (${stats.total})`, value: 'all' },
    { label: `5 Sao (${stats.counts[5]})`, value: '5' },
    { label: `4 Sao (${stats.counts[4]})`, value: '4' },
    { label: `3 Sao (${stats.counts[3]})`, value: '3' },
    { label: `2 Sao (${stats.counts[2]})`, value: '2' },
    { label: `1 Sao (${stats.counts[1]})`, value: '1' }
  ];
});

const replyModal = ref({
  show: false,
  review: null,
  text: ''
});

const openReplyModal = (review) => {
  replyModal.value.review = review;
  replyModal.value.text = '';
  replyModal.value.show = true;
};

const closeReplyModal = () => {
  replyModal.value.show = false;
  replyModal.value.review = null;
  replyModal.value.text = '';
};

const submitReply = () => {
  if (!replyModal.value.text.trim()) return;
  const review = mockReviews.value.find(r => r.id === replyModal.value.review.id);
  if (review) {
    review.reply = replyModal.value.text;
    review.reply_date = new Date().toLocaleDateString('vi-VN');
    showToast('Đã gửi phản hồi đánh giá thành công!');
  }
  closeReplyModal();
};

// Followers and Following Logic
const followModal = ref({
  show: false,
  type: 'followers',
  list: [],
  isLoading: false
});

const openFollowModal = async (type) => {
  followModal.value.type = type;
  followModal.value.show = true;
  followModal.value.list = [];
  followModal.value.isLoading = true;

  try {
    const endpoint = type === 'followers' ? `/api/users/${authStore.user?.id}/followers` : `/api/users/${authStore.user?.id}/followings`;
    const response = await axios.get(endpoint);
    if (response.data.success) {
      let users = response.data.data;

      if (type === 'followers') {
        const followingRes = await axios.get(`/api/users/${authStore.user?.id}/followings`);
        const followingIds = followingRes.data.data.map(u => u.id);
        users = users.map(user => ({
          ...user,
          isFollowing: followingIds.includes(user.id)
        }));
      }

      followModal.value.list = users;
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách theo dõi:', error);
  } finally {
    followModal.value.isLoading = false;
  }
};

const closeFollowModal = () => {
  followModal.value.show = false;
};

const unfollowUser = async (userId) => {
  try {
    const response = await axios.post(`/api/users/${userId}/follow`);
    if (response.data.success) {
      followModal.value.list = followModal.value.list.filter(u => u.id !== userId);
      if (profileData.value.followings_count > 0) profileData.value.followings_count--;
      showToast('Đã hủy theo dõi người dùng!');
    }
  } catch (error) {
    console.error('Lỗi khi thao tác:', error);
  }
};

const followBackUser = async (userId) => {
  try {
    const response = await axios.post(`/api/users/${userId}/follow`);
    if (response.data.success) {
      const user = followModal.value.list.find(u => u.id === userId);
      if (user) user.isFollowing = true;
      profileData.value.followings_count++;
      showToast('Đã theo dõi người dùng!');
    }
  } catch (error) {
    console.error('Lỗi khi thao tác:', error);
  }
};

// Administrative Units
const provinces = ref([]);
const wards = ref([]);

// Khởi tạo dữ liệu từ Store ngay lập tức để tránh nhấp nháy UI
const profileData = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
  address: authStore.user?.address || '',
  province_id: authStore.user?.province_id || '',
  province_name: authStore.user?.province_name || '',
  ward_id: authStore.user?.ward_id || '',
  ward_name: authStore.user?.ward_name || '',
  avatar: authStore.user?.avatar || '',
  followers_count: authStore.user?.followers_count || 0,
  followings_count: authStore.user?.followings_count || 0
});

// Đồng bộ lại nếu Store thay đổi (ví dụ khi Header nạp xong dữ liệu muộn hơn)
watch(() => authStore.user, (newUser) => {
  if (newUser) {
    profileData.value = {
      name: newUser.name,
      email: newUser.email,
      phone: newUser.phone,
      address: newUser.address,
      province_id: newUser.province_id || '',
      province_name: newUser.province_name || '',
      ward_id: newUser.ward_id || '',
      ward_name: newUser.ward_name || '',
      avatar: newUser.avatar,
      followers_count: newUser.followers_count || 0,
      followings_count: newUser.followings_count || 0
    };
    if (newUser.province_id) fetchInitialWards(newUser.province_id);
  }
}, { deep: true });

const onProvinceChange = async () => {
  profileData.value.ward_id = '';
  profileData.value.ward_name = '';
  wards.value = [];

  if (!profileData.value.province_id) {
    profileData.value.province_name = '';
    return;
  }

  const selected = provinces.value.find(p => p.code === profileData.value.province_id);
  profileData.value.province_name = selected ? selected.name : '';

  try {
    const res = await axios.get(`/api/locations/wards/${profileData.value.province_id}`);
    wards.value = res.data;
  } catch (error) {
    console.error('Failed to fetch wards:', error);
  }
};

const onWardChange = () => {
  const selected = wards.value.find(w => w.code === profileData.value.ward_id);
  profileData.value.ward_name = selected ? selected.name : '';
};

const fetchInitialWards = async (provinceId) => {
  try {
    const res = await axios.get(`/api/locations/wards/${provinceId}`);
    wards.value = res.data;
  } catch (error) {
    console.error('Failed to fetch initial wards:', error);
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

const passwordData = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const toast = ref({
  show: false,
  message: ''
});

const showToast = (message) => {
  toast.value.message = message;
  toast.value.show = true;
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const fetchProfile = async () => {
  try {
    const response = await axios.get('/api/auth/profile');
    const userData = response.data.data;
    profileData.value = {
      ...userData,
      followers_count: userData.followers_count || 0,
      followings_count: userData.followings_count || 0
    };
    // Cập nhật lại Store để Header đồng bộ theo
    authStore.setUser(userData);
  } catch (error) {
    console.error('Lỗi khi lấy thông tin cá nhân:', error);
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    errors.value.avatar = ['Dung lượng file không được vượt quá 2MB'];
    return;
  }

  avatarFile.value = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    profileData.value.avatar = e.target.result; // Base64 string
  };
  reader.readAsDataURL(file);
};

const updateProfile = async () => {
  loading.value = true;
  errors.value = {};

  try {
    let response;

    if (avatarFile.value) {
      const formData = new FormData();
      formData.append('_method', 'PUT');
      if (profileData.value.name) formData.append('name', profileData.value.name);
      if (profileData.value.phone) formData.append('phone', profileData.value.phone);
      if (profileData.value.address) formData.append('address', profileData.value.address);
      if (profileData.value.province_id) formData.append('province_id', profileData.value.province_id);
      if (profileData.value.province_name) formData.append('province_name', profileData.value.province_name);
      if (profileData.value.ward_id) formData.append('ward_id', profileData.value.ward_id);
      if (profileData.value.ward_name) formData.append('ward_name', profileData.value.ward_name);
      if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
      }

      response = await axios.post('/api/auth/profile', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    } else {
      const dataToSend = { ...profileData.value };
      delete dataToSend.avatar;
      response = await axios.put('/api/auth/profile', dataToSend);
    }

    const updatedUser = response.data.data;
    profileData.value = updatedUser;
    avatarFile.value = null;

    // QUAN TRỌNG: Cập nhật Store để Header thay đổi ngay lập tức
    authStore.setUser(updatedUser);

    showToast('Cập nhật hồ sơ thành công!');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};

const changePassword = async () => {
  passwordLoading.value = true;
  passwordErrors.value = {};

  try {
    await axios.put('/api/auth/profile/password', passwordData.value);
    passwordData.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    };
    showToast('Đổi mật khẩu thành công!');
  } catch (error) {
    if (error.response?.status === 422 || error.response?.status === 400) {
      passwordErrors.value = error.response.data.errors;
    }
  } finally {
    passwordLoading.value = false;
  }
};

onMounted(() => {
  fetchProvinces();
  fetchProfile().then(() => {
    if (profileData.value.province_id) {
      fetchInitialWards(profileData.value.province_id);
    }
  });
});
</script>
