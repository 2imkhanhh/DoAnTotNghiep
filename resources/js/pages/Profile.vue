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
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{ profileData.followers_count || 0 }}</div>
                <div class="text-[12px] font-medium text-on-surface-variant group-hover:text-primary transition-colors mt-0.5">Người theo dõi</div>
              </button>

              <button @click="openFollowModal('following')" type="button"
                class="text-center cursor-pointer focus:outline-none group">
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{ profileData.following_count || 0 }}</div>
                <div class="text-[12px] font-medium text-on-surface-variant group-hover:text-primary transition-colors mt-0.5">Đang theo dõi</div>
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
                <div class="text-5xl font-black text-on-surface mb-2">4.8</div>
                <div class="flex justify-center gap-1 mb-2 text-amber-500">
                  <span class="material-symbols-outlined font-variation-fill">star</span>
                  <span class="material-symbols-outlined font-variation-fill">star</span>
                  <span class="material-symbols-outlined font-variation-fill">star</span>
                  <span class="material-symbols-outlined font-variation-fill">star</span>
                  <span class="material-symbols-outlined font-variation-fill">star_half</span>
                </div>
                <p class="text-xs text-on-surface-variant">Trung bình trên 156 đánh giá</p>
              </div>

              <!-- Stars Breakdown -->
              <div class="col-span-2 space-y-2 md:pl-6">
                <!-- 5 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">5 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: 85%"></div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">85%</span>
                </div>
                <!-- 4 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">4 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: 10%"></div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">10%</span>
                </div>
                <!-- 3 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">3 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: 3%"></div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">3%</span>
                </div>
                <!-- 2 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">2 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: 1%"></div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">1%</span>
                </div>
                <!-- 1 star -->
                <div class="flex items-center gap-4">
                  <span class="text-xs font-bold text-on-surface-variant w-10">1 sao</span>
                  <div class="flex-1 bg-surface-container rounded-full h-2 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: 1%"></div>
                  </div>
                  <span class="text-xs text-on-surface-variant w-8 text-right font-bold">1%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Filter Options -->
          <div class="px-6 sm:px-8 py-4 border-b border-outline-variant flex flex-wrap gap-2">
            <button v-for="filter in ratingFilters" :key="filter.value" @click="currentRatingFilter = filter.value"
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
              <p class="font-medium">Chưa có đánh giá nào cho mức điểm này</p>
            </div>

            <div v-else v-for="rev in filteredReviews" :key="rev.id"
              class="border-b border-outline-variant last:border-0 pb-6 last:pb-0">
              <div class="flex items-start gap-4">
                <img :src="rev.reviewer_avatar"
                  class="w-10 h-10 rounded-full object-cover shrink-0 border border-outline-variant" />
                <div class="flex-1">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                    <h4 class="font-bold text-on-surface text-sm sm:text-base">{{ rev.reviewer_name }}</h4>
                    <span class="text-xs text-on-surface-variant">{{ rev.date }}</span>
                  </div>

                  <div class="flex items-center gap-1 text-amber-500 mb-2">
                    <span v-for="star in 5" :key="star" class="material-symbols-outlined text-sm font-variation-fill">
                      {{ star <= rev.rating ? 'star' : 'star_outline' }} </span>
                        <span class="text-xs text-on-surface-variant ml-2 font-medium">Mua hàng: <span
                            class="text-primary hover:underline cursor-pointer font-bold">{{ rev.post_title
                            }}</span></span>
                  </div>

                  <p class="text-on-surface text-sm leading-relaxed mb-3">
                    {{ rev.comment }}
                  </p>

                  <!-- Seller Response (if any) -->
                  <div v-if="rev.reply"
                    class="bg-surface-container p-3 rounded-xl border border-outline-variant text-xs sm:text-sm mt-2 relative">
                    <div class="absolute top-3 left-4 w-1.5 h-1.5 bg-primary rounded-full"></div>
                    <div class="pl-4">
                      <div class="flex items-center gap-2 mb-1">
                        <span class="font-bold text-on-surface">Phản hồi của bạn</span>
                        <span class="text-[10px] text-on-surface-variant font-medium">{{ rev.reply_date }}</span>
                      </div>
                      <p class="text-on-surface-variant leading-relaxed">
                        {{ rev.reply }}
                      </p>
                    </div>
                  </div>
                  <div v-else class="mt-2 flex justify-end">
                    <button @click="openReplyModal(rev)"
                      class="text-xs text-primary hover:underline font-bold flex items-center gap-1 cursor-pointer">
                      <span class="material-symbols-outlined text-sm">reply</span>
                      Phản hồi đánh giá
                    </button>
                  </div>
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
    <div v-if="followModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300"
        @click="closeFollowModal"></div>
      <div
        class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200 relative z-10">
        <div class="p-5 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
          <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">
              {{ followModal.type === 'followers' ? 'group' : 'person_add' }}
            </span>
            {{ followModal.type === 'followers' ? 'Người theo dõi' : 'Đang theo dõi' }}
          </h3>
          <button @click="closeFollowModal"
            class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container transition-colors cursor-pointer focus:outline-none flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>
        <div class="p-4 max-h-[350px] overflow-y-auto space-y-2">
          <div v-for="user in followModal.list" :key="user.id"
            class="flex items-center justify-between p-2 rounded-xl hover:bg-surface-container-low transition-colors border border-transparent hover:border-outline-variant">
            <div class="flex items-center gap-3">
              <img :src="user.avatar || 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'"
                class="w-9 h-9 rounded-full object-cover border border-outline-variant" />
              <div class="min-w-0">
                <div class="font-bold text-on-surface text-sm truncate w-40 sm:w-48">{{ user.name }}</div>
                <div class="text-[11px] text-on-surface-variant">@{{ user.username }}</div>
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
          <div v-if="followModal.list.length === 0" class="text-center py-8 text-on-surface-variant">
            <span class="material-symbols-outlined text-4xl mb-2 opacity-40">group</span>
            <p class="font-medium text-sm">Danh sách này trống</p>
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

const mockReviews = ref([
  {
    id: 1,
    reviewer_name: 'Nguyễn Văn Hùng',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Nguyen+Van+Hung&background=3b82f6&color=fff',
    rating: 5,
    date: '12/05/2026',
    post_title: 'iPhone 13 Pro Max 256GB Gold',
    comment: 'Điện thoại dùng rất tốt, pin còn 92% đúng như chủ thớt mô tả. Ngoại hình đẹp keng xà beng, chủ shop hỗ trợ ship cod nhanh cực kỳ, đóng gói cẩn thận 3 lớp chống sốc luôn. Rất uy tín nha mọi người!',
    reply: 'Cảm ơn bác Hùng đã tin tưởng ủng hộ shop nhé! Có vấn đề gì cần hỗ trợ cứ nhắn tin trực tiếp cho em nha.',
    reply_date: '12/05/2026'
  },
  {
    id: 2,
    reviewer_name: 'Trần Thị Lan',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Tran+Thi+Lan&background=10b981&color=fff',
    rating: 5,
    date: '08/05/2026',
    post_title: 'MacBook Air M1 8GB/256GB Gray',
    comment: 'Máy dùng siêu mượt, bàn phím và màn hình không một vết xước. Giao dịch trực tiếp tại nhà nhanh gọn lẹ, anh chủ nhiệt tình test máy giúp mình từ A-Z. Rất recommend mua đồ cũ ở đây!',
    reply: null,
    reply_date: null
  },
  {
    id: 3,
    reviewer_name: 'Lê Minh Tuấn',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Le+Minh+Tuan&background=f59e0b&color=fff',
    rating: 4,
    date: '30/04/2026',
    post_title: 'Xe máy Honda Wave Alpha 2021',
    comment: 'Xe chạy êm, máy zin. Chỉ có lốp xe hơi mòn tí phải đi thay nhưng với giá này thì quá hời rồi. Bác bán hàng vui tính, bớt cho mình 200k tiền xăng xe đi lại nữa.',
    reply: 'Cảm ơn bạn đã phản hồi! Do xe cũng đi được một thời gian nên lốp hơi mòn, mình đã chủ động bớt lộc xăng xe để bạn làm lại lốp rồi nhé. Chúc bạn vạn dặm bình an!',
    reply_date: '30/04/2026'
  },
  {
    id: 4,
    reviewer_name: 'Phạm Thanh Sơn',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Pham+Thanh+Son&background=ef4444&color=fff',
    rating: 3,
    date: '15/04/2026',
    post_title: 'Tai nghe Bluetooth Sony WH-1000XM4',
    comment: 'Tai nghe chất âm tốt, chống ồn đỉnh. Tuy nhiên đệm da hơi sờn nhẹ ở góc mà trong tin đăng chưa nói rõ. Nhưng giao dịch nhanh nên vẫn vote 4 sao trừ 1 sao ngoại hình.',
    reply: 'Dạ shop xin lỗi vì sơ sót không chụp kỹ góc sờn đó nhé ạ. Lần sau shop sẽ lưu ý mô tả chi tiết hơn. Cảm ơn phản hồi đóng góp của bạn!',
    reply_date: '16/04/2026'
  },
  {
    id: 5,
    reviewer_name: 'Hoàng Ngọc Ánh',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Hoang+Ngoc+Anh&background=ec4899&color=fff',
    rating: 5,
    date: '02/04/2026',
    post_title: 'Tủ lạnh Samsung Inverter 236L',
    comment: 'Tủ lạnh chạy cực êm, không ồn tí nào, làm lạnh nhanh. Giao hàng hỗ trợ khiêng lên tận lầu 3 giúp mình luôn, quá nhiệt tình luôn ạ. 10 điểm không có nhưng!',
    reply: null,
    reply_date: null
  }
]);

const filteredReviews = computed(() => {
  if (currentRatingFilter.value === 'all') return mockReviews.value;
  return mockReviews.value.filter(rev => rev.rating === parseInt(currentRatingFilter.value));
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
const mockFollowers = ref([
  { id: 101, name: 'Nguyễn Văn Hùng', username: 'hung_nv', avatar: 'https://ui-avatars.com/api/?name=Nguyen+Van+Hung&background=3b82f6&color=fff', isFollowing: true },
  { id: 102, name: 'Trần Thị Lan', username: 'lan_tranthi', avatar: 'https://ui-avatars.com/api/?name=Tran+Thi+Lan&background=10b981&color=fff', isFollowing: false },
  { id: 103, name: 'Lê Minh Tuấn', username: 'tuan_leminh', avatar: 'https://ui-avatars.com/api/?name=Le+Minh+Tuan&background=f59e0b&color=fff', isFollowing: true },
  { id: 104, name: 'Phạm Thanh Sơn', username: 'son_pham', avatar: 'https://ui-avatars.com/api/?name=Pham+Thanh+Son&background=ef4444&color=fff', isFollowing: false },
  { id: 105, name: 'Hoàng Ngọc Ánh', username: 'anh_hoang', avatar: 'https://ui-avatars.com/api/?name=Hoang+Ngoc+Anh&background=ec4899&color=fff', isFollowing: true }
]);

const mockFollowing = ref([
  { id: 101, name: 'Nguyễn Văn Hùng', username: 'hung_nv', avatar: 'https://ui-avatars.com/api/?name=Nguyen+Van+Hung&background=3b82f6&color=fff' },
  { id: 103, name: 'Lê Minh Tuấn', username: 'tuan_leminh', avatar: 'https://ui-avatars.com/api/?name=Le+Minh+Tuan&background=f59e0b&color=fff' },
  { id: 105, name: 'Hoàng Ngọc Ánh', username: 'anh_hoang', avatar: 'https://ui-avatars.com/api/?name=Hoang+Ngoc+Anh&background=ec4899&color=fff' },
  { id: 106, name: 'Đỗ Thùy Chi', username: 'chi_thuy_do', avatar: 'https://ui-avatars.com/api/?name=Do+Thuy+Chi&background=8b5cf6&color=fff' },
  { id: 107, name: 'Vũ Quốc Bảo', username: 'bao_vuquoc', avatar: 'https://ui-avatars.com/api/?name=Vu+Quoc+Bao&background=06b6d4&color=fff' }
]);

const followModal = ref({
  show: false,
  type: 'followers',
  list: []
});

const openFollowModal = (type) => {
  followModal.value.type = type;
  followModal.value.list = type === 'followers' ? [...mockFollowers.value] : [...mockFollowing.value];
  followModal.value.show = true;
};

const closeFollowModal = () => {
  followModal.value.show = false;
};

const unfollowUser = (userId) => {
  mockFollowing.value = mockFollowing.value.filter(u => u.id !== userId);

  // Also update isFollowing status in followers list if they are present there
  const follower = mockFollowers.value.find(u => u.id === userId);
  if (follower) {
    follower.isFollowing = false;
  }

  profileData.value.following_count = mockFollowing.value.length;
  // Update modal list if open
  if (followModal.value.show && followModal.value.type === 'following') {
    followModal.value.list = [...mockFollowing.value];
  }
  showToast('Đã hủy theo dõi người dùng!');
};

const followBackUser = (userId) => {
  const follower = mockFollowers.value.find(u => u.id === userId);
  if (follower) {
    follower.isFollowing = true;

    // Add to following list
    if (!mockFollowing.value.some(u => u.id === userId)) {
      mockFollowing.value.push({
        id: follower.id,
        name: follower.name,
        username: follower.username,
        avatar: follower.avatar
      });
    }
  }

  profileData.value.following_count = mockFollowing.value.length;
  // Update modal list if open
  if (followModal.value.show && followModal.value.type === 'followers') {
    followModal.value.list = [...mockFollowers.value];
  }
  showToast('Đã theo dõi người dùng!');
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
  followers_count: authStore.user?.followers_count !== undefined ? authStore.user?.followers_count : mockFollowers.value.length,
  following_count: authStore.user?.following_count !== undefined ? authStore.user?.following_count : mockFollowing.value.length
});

// Đồng bộ lại nếu Store thay đổi (ví dụ khi Header nạp xong dữ liệu muộn hơn)
watch(() => authStore.user, (newUser) => {
  if (newUser) {
    profileData.value = {
      name: newUser.name,
      email: newUser.email,
      phone: newUser.phone,
      address: newUser.address,
      province_id: newUser.province_id,
      province_name: newUser.province_name,
      ward_id: newUser.ward_id,
      ward_name: newUser.ward_name,
      avatar: newUser.avatar,
      followers_count: newUser.followers_count !== undefined ? newUser.followers_count : mockFollowers.value.length,
      following_count: newUser.following_count !== undefined ? newUser.following_count : mockFollowing.value.length
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
      followers_count: userData.followers_count !== undefined ? userData.followers_count : mockFollowers.value.length,
      following_count: userData.following_count !== undefined ? userData.following_count : mockFollowing.value.length
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
    const response = await axios.put('/api/auth/profile', profileData.value);
    const updatedUser = response.data.data;
    profileData.value = updatedUser;

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
