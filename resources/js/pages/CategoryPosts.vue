<template>
  <div class="w-full max-w-7xl mx-auto space-y-4 pb-12 px-4 mt-4">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-2">
      <router-link to="/" class="hover:text-primary transition-colors">Trang chủ</router-link>
      <span class="material-symbols-outlined text-[12px]">chevron_right</span>
      <span class="font-semibold text-primary">{{ categoryName }}</span>
    </div>

    <!-- MAIN FILTER INTERFACE (Exactly like the image) -->
    <div class="bg-white rounded-2xl shadow-xs border border-outline-variant/60 p-4 space-y-4">

      <!-- Row 1: Filter Pills -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Active Category Capsule (Dark pill with close icon) / General Category Pill -->
        <div class="relative category-dropdown-wrapper">
          <div v-if="categoryId" @click.stop="toggleCategoryDropdown"
            class="filter-pill-dark cursor-pointer flex items-center gap-1 hover:bg-black/95 transition-colors">
            <span>{{ categoryName }}</span>
            <button @click.stop="clearCategoryFilter" class="clear-cat-btn" aria-label="Xoá danh mục">
              ×
            </button>
          </div>

          <button v-else @click="toggleCategoryDropdown" class="filter-pill">
            <span>Danh mục</span>
            <span class="material-symbols-outlined text-sm ml-1">keyboard_arrow_down</span>
          </button>

          <!-- Modern Multi-level Category Selection Dropdown (Exactly like Chotot screenshots!) -->
          <div v-if="showCategoryDropdown"
            class="absolute left-0 mt-2 w-80 bg-white border border-outline-variant rounded-2xl shadow-xl z-30 overflow-hidden flex flex-col">
            <!-- Header navigation bar -->
            <div
              class="flex items-center justify-between px-4 py-3 border-b border-outline-variant/60 bg-surface-container-low">
              <button v-if="menuHistory.length > 0" @click.stop="goBackCategoryLevel"
                class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-surface-container-high transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[20px] text-on-surface">arrow_back</span>
              </button>
              <button v-else @click.stop="toggleCategoryDropdown"
                class="flex items-center justify-center w-8 h-8 rounded-full hover:bg-surface-container-high transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[20px] text-on-surface">close</span>
              </button>

              <span class="font-bold text-sm text-on-surface">Chọn danh mục</span>
              <div class="w-8"></div> <!-- Spacer to align text center -->
            </div>

            <!-- Category options list scrollable -->
            <div class="max-h-80 overflow-y-auto no-scrollbar py-1">
              <button v-for="cat in currentCategoryMenu" :key="cat.id" @click.stop="handleCategoryClick(cat)"
                class="province-option-btn flex items-center justify-between w-full px-4 py-3 text-left hover:bg-surface-container-high transition-colors cursor-pointer">
                <span class="text-xs font-semibold text-on-surface">{{ cat.name }}</span>

                <!-- Right side icons: arrow if has children, empty/filled circle checkbox if leaf node -->
                <span v-if="cat.children && cat.children.length > 0"
                  class="material-symbols-outlined text-[18px] text-gray-400">
                  chevron_right
                </span>
                <span v-else-if="categoryId === cat.id || (cat.isAllOption && categoryId === cat.parentCategory.id)"
                  class="material-symbols-outlined text-[20px] text-primary font-variation-fill">
                  check_circle
                </span>
                <span v-else class="material-symbols-outlined text-[20px] text-gray-300">
                  radio_button_unchecked
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Price dropdown pill -->
        <div class="relative price-dropdown-wrapper">
          <button @click="togglePriceDropdown" class="filter-pill" :class="{ 'active-filter': minPrice || maxPrice }">
            <span>Giá</span>
            <span class="material-symbols-outlined text-sm ml-1 transition-transform"
              :class="{ 'rotate-180': showPriceDropdown }">keyboard_arrow_down</span>
          </button>

          <!-- Price Dropdown Box -->
          <div v-if="showPriceDropdown"
            class="absolute left-0 mt-2 w-80 bg-white border border-outline-variant rounded-2xl shadow-xl z-30 p-4 space-y-4">
            <h4 class="font-bold text-sm text-on-surface">Chọn khoảng giá</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <button @click="setPriceRange(null, null)" class="price-range-btn">Tất cả</button>
              <button @click="setPriceRange(0, 2000000)" class="price-range-btn">Dưới 2 triệu</button>
              <button @click="setPriceRange(2000000, 5000000)" class="price-range-btn">2 - 5 triệu</button>
              <button @click="setPriceRange(5000000, 10000000)" class="price-range-btn">5 - 10 triệu</button>
              <button @click="setPriceRange(10000000, null)" class="price-range-btn col-span-2">Trên 10 triệu</button>
            </div>

            <div class="border-t border-outline-variant pt-3 space-y-3">
              <div class="text-xs font-bold text-on-surface-variant">Hoặc nhập khoảng giá tự chọn:</div>
              <div class="flex items-center gap-2">
                <input :value="formattedMinPrice" @input="handleMinPriceInput" type="text" placeholder="Tối thiểu"
                  class="price-input">
                <span class="text-gray-400">-</span>
                <input :value="formattedMaxPrice" @input="handleMaxPriceInput" type="text" placeholder="Tối đa"
                  class="price-input">
              </div>
              <button @click="applyCustomPrice"
                class="w-full py-2 bg-primary text-white font-bold rounded-xl text-xs hover:bg-primary/95 transition-colors">
                Áp dụng
              </button>
            </div>
          </div>
        </div>

        <!-- Dynamic Category Attribute Filter Dropdowns -->
        <div v-for="attr in categoryAttributes" :key="attr.key"
          class="relative" :class="`attr-dropdown-${attr.key}`">
          <button @click="toggleAttrDropdown(attr.key)"
            class="filter-pill"
            :class="{ 'active-filter': selectedSpecs[attr.key] }">
            <span>{{ selectedSpecs[attr.key] || attr.name }}</span>
            <span class="material-symbols-outlined text-sm ml-1 transition-transform"
              :class="{ 'rotate-180': showAttrDropdowns[attr.key] }">keyboard_arrow_down</span>
          </button>

          <div v-if="showAttrDropdowns[attr.key]"
            class="absolute left-0 mt-2 w-56 bg-white border border-outline-variant rounded-2xl shadow-xl z-30 overflow-hidden py-2"
            @click.stop>
            <div class="px-4 py-2 font-bold text-xs text-on-surface-variant uppercase tracking-wider">{{ attr.name }}</div>
            <!-- Tất cả -->
            <button @click="selectSpec(attr.key, null)" class="sort-option-btn">
              <span class="text-xs font-semibold">Tất cả</span>
              <span v-if="!selectedSpecs[attr.key]" class="material-symbols-outlined text-[20px] text-primary font-variation-fill">check_circle</span>
              <span v-else class="material-symbols-outlined text-[20px] text-gray-300">radio_button_unchecked</span>
            </button>
            <!-- Các option -->
            <button v-for="opt in attr.options" :key="opt" @click="selectSpec(attr.key, opt)" class="sort-option-btn">
              <span class="text-xs font-semibold">{{ opt }}</span>
              <span v-if="selectedSpecs[attr.key] === opt" class="material-symbols-outlined text-[20px] text-primary font-variation-fill">check_circle</span>
              <span v-else class="material-symbols-outlined text-[20px] text-gray-300">radio_button_unchecked</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Row 2: Location shortcuts & Full Province Dropdown Select Box -->
      <div class="flex items-center gap-2 py-1 border-t border-dashed border-outline-variant/60">
        <span class="text-xs font-bold text-on-surface-variant shrink-0">Khu vực:</span>
        <div class="flex flex-wrap items-center gap-2 grow">
          <button @click="selectProvince('')" :class="['loc-pill', { 'active': activeProvince === '' }]">
            Toàn quốc
          </button>

          <!-- Premium Custom Province Selector Popover (Beautiful, fully styleable and search-friendly!) -->
          <div class="relative province-dropdown-wrapper">
            <button @click="toggleProvinceDropdown" class="loc-pill" :class="{ 'active': isCustomProvince }">
              <span class="material-symbols-outlined text-[16px]">map</span>
              <span>{{ isCustomProvince ? activeProvince : 'Tỉnh thành khác' }}</span>
              <span class="material-symbols-outlined text-sm ml-1 transition-transform"
                :class="{ 'rotate-180': showProvinceDropdown }">keyboard_arrow_down</span>
            </button>

            <div v-if="showProvinceDropdown"
              class="absolute left-0 mt-2 w-72 bg-white border border-outline-variant rounded-2xl shadow-xl z-30 p-3 space-y-2">
              <div
                class="flex items-center gap-1.5 px-3 py-2 bg-surface-container-high rounded-xl border border-outline-variant/60">
                <span class="material-symbols-outlined text-[18px] text-gray-400">search</span>
                <input v-model="provinceSearchQuery" type="text" placeholder="Tìm tỉnh thành..."
                  class="w-full bg-transparent border-none text-xs outline-none text-on-surface">
              </div>

              <div class="max-h-60 overflow-y-auto no-scrollbar scroll-smooth space-y-1">
                <button @click="selectProvince('')" class="province-option-btn"
                  :class="{ 'active': activeProvince === '' }">
                  Toàn quốc
                </button>
                <button v-for="prov in filteredProvinces" :key="prov.code" @click="selectCustomProvince(prov.name)"
                  class="province-option-btn" :class="{ 'active': activeProvince === prov.name }">
                  {{ prov.name }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SORTING BAR (Exactly like the image dropdown menu) -->
    <div class="flex items-center justify-between border-b border-outline-variant/60 pb-2 mt-4">
      <div class="text-xs font-bold text-on-surface-variant">
        Tìm thấy {{ posts.length }} tin đăng phù hợp
      </div>

      <!-- Custom Sort Dropdown (Exactly matching the user screenshot!) -->
      <div class="relative sort-dropdown-wrapper">
        <button @click="toggleSortDropdown"
          class="flex items-center gap-1 bg-white hover:bg-surface-container-high px-3 py-1.5 rounded-xl border border-outline-variant text-xs font-bold text-on-surface cursor-pointer select-none">
          <span>{{ getSortLabel(activeSort) }}</span>
          <span class="material-symbols-outlined text-[18px] transition-transform"
            :class="{ 'rotate-180': showSortDropdown }">keyboard_arrow_down</span>
        </button>

        <!-- Dropdown Box matching image -->
        <div v-if="showSortDropdown"
          class="absolute right-0 mt-2 w-64 bg-white border border-outline-variant rounded-2xl shadow-xl z-30 overflow-hidden py-2">
          <div class="px-4 py-2 font-bold text-xs text-on-surface-variant uppercase tracking-wider">Sắp xếp theo</div>

          <button @click="selectSort('latest')" class="sort-option-btn">
            <span class="text-xs font-semibold">Tin mới nhất</span>
            <span v-if="activeSort === 'latest'" class="material-symbols-outlined text-[20px] text-primary font-variation-fill">
              check_circle
            </span>
            <span v-else class="material-symbols-outlined text-[20px] text-gray-300">
              radio_button_unchecked
            </span>
          </button>

          <button @click="selectSort('price_asc')" class="sort-option-btn">
            <span class="text-xs font-semibold">Giá thấp trước</span>
            <span v-if="activeSort === 'price_asc'" class="material-symbols-outlined text-[20px] text-primary font-variation-fill">
              check_circle
            </span>
            <span v-else class="material-symbols-outlined text-[20px] text-gray-300">
              radio_button_unchecked
            </span>
          </button>

          <button @click="selectSort('price_desc')" class="sort-option-btn">
            <span class="text-xs font-semibold">Giá cao trước</span>
            <span v-if="activeSort === 'price_desc'" class="material-symbols-outlined text-[20px] text-primary font-variation-fill">
              check_circle
            </span>
            <span v-else class="material-symbols-outlined text-[20px] text-gray-300">
              radio_button_unchecked
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- PRODUCTS RENDER (ALWAYS HORIZONTAL LIST LAYOUT MATCHING IMAGE) -->
    <LoadingState v-if="loading" />

    <div v-else>
      <div v-if="posts.length > 0" class="flex flex-col mt-2">
        <div v-for="post in posts" :key="post.id"
          @click="router.push(`/post/${post.slug}`)"
          class="group py-4 px-3 -mx-3 border-b border-slate-200 transition-all duration-300 ease-out flex flex-row gap-4 relative cursor-pointer hover:bg-white hover:shadow-xl hover:shadow-slate-200/60 hover:-translate-y-1 hover:border-transparent hover:z-10 rounded-2xl">

          <!-- Left Side Image -->
          <router-link :to="`/post/${post.slug}`"
            class="relative w-[100px] h-[100px] sm:w-[120px] sm:h-[120px] shrink-0 rounded-lg overflow-hidden block bg-slate-100">
            <img :src="getPrimaryImage(post)" :alt="post.title"
              class="w-full h-full object-cover">
            <span v-if="post.status === 'sold'"
              class="absolute top-1 left-1 bg-black/60 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm backdrop-blur-sm z-10">
              Đã bán
            </span>
          </router-link>

          <!-- Right Side Details -->
          <div class="flex flex-col grow justify-between">
            <div>
              <router-link :to="`/post/${post.slug}`"
                class="text-[14px] sm:text-[16px] text-slate-800 line-clamp-2 transition-colors group-hover:text-primary font-medium">
                {{ post.title }}
              </router-link>
              <p class="text-[#d0021b] font-bold text-[15px] sm:text-[16px] mt-1">{{ formatPrice(post.price) }} đ</p>
            </div>

            <div class="flex items-end justify-between mt-2">
              <div class="flex flex-wrap items-center text-[13px] text-[#9b9b9b] gap-1.5">
                <div class="flex items-center gap-1.5">
                  <img
                    :src="post.user?.avatar || 'https://ui-avatars.com/api/?name=' + (post.user?.name || 'User') + '&background=random'"
                    class="w-5 h-5 rounded-full object-cover">
                  <span>{{ post.user?.name || 'Cá Nhân' }}</span>
                </div>
                <span>&middot;</span>
                <span>{{ formatTime(post.created_at) }}</span>
                <span>&middot;</span>
                <span>{{ [post.ward_name, post.province_name].filter(Boolean).join(', ') }}</span>
              </div>

              <!-- Favorite Heart Button -->
              <div class="flex items-center gap-2 shrink-0 ml-4 relative z-20">
                <button v-if="!authStore.isLoggedIn || post.user_id !== authStore.user?.id"
                  @click.prevent.stop="toggleFavorite(post.id)"
                  class="w-8 h-8 flex items-center justify-center cursor-pointer transition-all duration-300 hover:scale-125 active:scale-95 group/heart">
                  <span :class="['material-symbols-outlined text-[22px] text-error font-variation-fill transition-all duration-300 absolute',
                    isFavorite(post.id) ? 'opacity-100 scale-100' : 'opacity-0 scale-0']">
                    favorite
                  </span>
                  <span :class="['material-symbols-outlined text-[26px] text-gray-800 absolute transition-opacity duration-300',
                    isFavorite(post.id) ? 'opacity-0' : 'opacity-100']">
                    favorite
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else
        class="py-20 text-center text-on-surface-variant bg-surface-container-low rounded-2xl border-2 border-dashed border-outline-variant mt-2">
        <span class="material-symbols-outlined text-5xl mb-3 text-outline">inventory_2</span>
        <h3 class="text-xl font-bold mb-2">Không tìm thấy tin đăng phù hợp</h3>
        <p class="max-w-md mx-auto mb-6 text-sm text-outline">Hãy thử thay đổi bộ lọc khoảng giá, chọn tỉnh thành khác
          hoặc quay lại xem tất cả bài đăng bán sản phẩm.</p>
        <button @click="resetFilters"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-on-primary hover:bg-primary/90 font-bold rounded-xl shadow-xs transition-colors cursor-pointer">
          <span class="material-symbols-outlined">restart_alt</span>
          Đặt lại bộ lọc
        </button>
      </div>

      <!-- Pagination / Load More -->
      <div v-if="posts.length > 0" class="flex justify-center items-center gap-4 mt-12">
        <button v-if="hasMore" @click="loadMorePosts" :disabled="loadingMore" class="btn-load-more">
          {{ loadingMore ? 'Đang tải...' : 'Xem thêm tin khác' }}
        </button>

        <button v-if="currentPage > 1" @click="collapsePosts" class="btn-collapse">
          Ẩn bớt tin
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import LoadingState from '../components/common/LoadingState.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Basic states
const categoryName = ref('...');
const categoryId = ref(null);
const subcategories = ref([]);
const posts = ref([]);
const favoriteIds = ref([]);
const allCategories = ref([]);

const loading = ref(true);
const loadingMore = ref(false);
const currentPage = ref(1);
const hasMore = ref(true);

// Filter states
const activeProvince = ref('');
const activeSort = ref('latest');
const minPrice = ref(null);
const maxPrice = ref(null);
const minPriceInput = ref('');
const maxPriceInput = ref('');
const showPriceDropdown = ref(false);
const showSortDropdown = ref(false);
const showProvinceDropdown = ref(false);
const showCategoryDropdown = ref(false);
const provinceSearchQuery = ref('');
const searchKeyword = ref(route.query.search || '');

// Dynamic Category Attribute Filter states
const categoryAttributes = ref([]); // Danh sách attribute có type=select của danh mục hiện tại
const selectedSpecs = ref({}); // Giá trị đang chọn: { brand: 'Apple', condition: 'Mới' }
const showAttrDropdowns = ref({}); // Trạng thái mở/đóng: { brand: false }

// Full categories tree menu state (For Chotot sliding multi-level menu)
const currentCategoryMenu = ref([]);
const menuHistory = ref([]);

// Full provinces list loaded from API
const allProvinces = ref([]);

// Computed to check if the current activeProvince is a custom select option
const isCustomProvince = computed(() => {
  return activeProvince.value !== '';
});

// Computed values for dynamic dot formatting on price inputs (legible separated thousands)
const formattedMinPrice = computed(() => {
  if (minPriceInput.value === null || minPriceInput.value === '') return '';
  return new Intl.NumberFormat('vi-VN').format(minPriceInput.value);
});

const formattedMaxPrice = computed(() => {
  if (maxPriceInput.value === null || maxPriceInput.value === '') return '';
  return new Intl.NumberFormat('vi-VN').format(maxPriceInput.value);
});

const handleMinPriceInput = (e) => {
  const value = e.target.value.replace(/\D/g, '');
  minPriceInput.value = value ? parseInt(value) : '';
};

const handleMaxPriceInput = (e) => {
  const value = e.target.value.replace(/\D/g, '');
  maxPriceInput.value = value ? parseInt(value) : '';
};

// Computed to filter provinces in search box
const filteredProvinces = computed(() => {
  if (!provinceSearchQuery.value) return allProvinces.value;
  const q = provinceSearchQuery.value.toLowerCase().trim();
  return allProvinces.value.filter(p => p.name.toLowerCase().includes(q));
});



const isFavorite = (postId) => {
  return favoriteIds.value.includes(Number(postId));
};

const togglePriceDropdown = () => {
  showPriceDropdown.value = !showPriceDropdown.value;
  showSortDropdown.value = false;
  showProvinceDropdown.value = false;
  showCategoryDropdown.value = false;
  showAttrDropdowns.value = {};
};

const toggleSortDropdown = () => {
  showSortDropdown.value = !showSortDropdown.value;
  showPriceDropdown.value = false;
  showProvinceDropdown.value = false;
  showCategoryDropdown.value = false;
  showAttrDropdowns.value = {};
};

const toggleProvinceDropdown = () => {
  showProvinceDropdown.value = !showProvinceDropdown.value;
  showPriceDropdown.value = false;
  showSortDropdown.value = false;
  showCategoryDropdown.value = false;
  showAttrDropdowns.value = {};
  provinceSearchQuery.value = '';
};

// Toggle a specific attribute dropdown
const toggleAttrDropdown = (key) => {
  const isOpen = showAttrDropdowns.value[key];
  // Đóng tất cả dropdowns khác
  showPriceDropdown.value = false;
  showSortDropdown.value = false;
  showProvinceDropdown.value = false;
  showCategoryDropdown.value = false;
  showAttrDropdowns.value = {};
  // Toggle dropdown này
  if (!isOpen) {
    showAttrDropdowns.value[key] = true;
  }
};

// Chọn một giá trị specification
const selectSpec = (key, value) => {
  if (value === null) {
    const newSpecs = { ...selectedSpecs.value };
    delete newSpecs[key];
    selectedSpecs.value = newSpecs;
  } else {
    selectedSpecs.value = { ...selectedSpecs.value, [key]: value };
  }
  showAttrDropdowns.value = {};
  reloadPosts();
};

// Manage multi-level Category Dropdown trigger and resets
const toggleCategoryDropdown = () => {
  showCategoryDropdown.value = !showCategoryDropdown.value;
  if (showCategoryDropdown.value) {
    if (categoryId.value) {
      const parent = findParentCategory(allCategories.value, categoryId.value);
      if (parent) {
        menuHistory.value = [parent];
        const allOption = {
          id: 'all-' + parent.id,
          name: 'Tất cả',
          slug: parent.slug,
          isAllOption: true,
          parentCategory: parent,
          children: []
        };
        currentCategoryMenu.value = [allOption, ...parent.children];
      } else {
        const currentCat = allCategories.value.find(c => c.id === categoryId.value);
        if (currentCat && currentCat.children && currentCat.children.length > 0) {
          menuHistory.value = [currentCat];
          const allOption = {
            id: 'all-' + currentCat.id,
            name: 'Tất cả',
            slug: currentCat.slug,
            isAllOption: true,
            parentCategory: currentCat,
            children: []
          };
          currentCategoryMenu.value = [allOption, ...currentCat.children];
        } else {
          currentCategoryMenu.value = [...allCategories.value];
          menuHistory.value = [];
        }
      }
    } else {
      currentCategoryMenu.value = [...allCategories.value];
      menuHistory.value = [];
    }
  }
  showProvinceDropdown.value = false;
  showPriceDropdown.value = false;
  showSortDropdown.value = false;
};

// Handle category clicks - slides forward if children exist, selects directly if leaf
const handleCategoryClick = (cat) => {
  if (cat.isAllOption) {
    selectCategory(cat.parentCategory);
    return;
  }

  if (cat.children && cat.children.length > 0) {
    menuHistory.value.push(cat);
    const allOption = {
      id: 'all-' + cat.id,
      name: 'Tất cả',
      slug: cat.slug,
      isAllOption: true,
      parentCategory: cat,
      children: []
    };
    currentCategoryMenu.value = [allOption, ...cat.children];
  } else {
    selectCategory(cat);
  }
};

// Return to previous parent category level
const goBackCategoryLevel = () => {
  if (menuHistory.value.length > 0) {
    menuHistory.value.pop();
    if (menuHistory.value.length > 0) {
      const parent = menuHistory.value[menuHistory.value.length - 1];
      const allOption = {
        id: 'all-' + parent.id,
        name: 'Tất cả',
        slug: parent.slug,
        isAllOption: true,
        parentCategory: parent,
        children: []
      };
      currentCategoryMenu.value = [allOption, ...parent.children];
    } else {
      currentCategoryMenu.value = [...allCategories.value];
    }
  }
};

const getSortLabel = (sortValue) => {
  if (sortValue === 'price_asc') return 'Giá thấp trước';
  if (sortValue === 'price_desc') return 'Giá cao trước';
  return 'Tin mới nhất';
};

const selectSort = (sortValue) => {
  activeSort.value = sortValue;
  showSortDropdown.value = false;
  reloadPosts();
};

const setPriceRange = (min, max) => {
  minPrice.value = min;
  maxPrice.value = max;
  minPriceInput.value = min !== null ? min : '';
  maxPriceInput.value = max !== null ? max : '';
  showPriceDropdown.value = false;
  reloadPosts();
};

const applyCustomPrice = () => {
  minPrice.value = minPriceInput.value !== '' ? Number(minPriceInput.value) : null;
  maxPrice.value = maxPriceInput.value !== '' ? Number(maxPriceInput.value) : null;
  showPriceDropdown.value = false;
  reloadPosts();
};

const selectProvince = (prov) => {
  activeProvince.value = prov;
  showProvinceDropdown.value = false;
  reloadPosts();
};

const selectCustomProvince = (provName) => {
  activeProvince.value = provName;
  showProvinceDropdown.value = false;
  reloadPosts();
};

const clearCategoryFilter = () => {
  categoryId.value = null;
  categoryName.value = 'Danh mục';
  subcategories.value = [];
  categoryAttributes.value = [];
  selectedSpecs.value = {};
  showAttrDropdowns.value = {};
  showCategoryDropdown.value = false;
  router.push('/marketplace');
};

const selectCategory = (cat) => {
  categoryId.value = cat.id;
  categoryName.value = cat.name;
  subcategories.value = cat.children || [];
  selectedSpecs.value = {}; // Reset specs khi đổi danh mục
  showAttrDropdowns.value = {};
  showCategoryDropdown.value = false;
  if (cat.slug) {
    router.push(`/category/${cat.slug}`);
  } else {
    router.push('/marketplace');
  }
};

// Event when selecting custom province from full dropdown list
const onCustomProvinceChange = (e) => {
  selectProvince(e.target.value);
};

const resetFilters = () => {
  activeProvince.value = '';
  activeSort.value = 'latest';
  minPrice.value = null;
  maxPrice.value = null;
  minPriceInput.value = '';
  maxPriceInput.value = '';
  selectedSpecs.value = {};
  reloadPosts();
};

const fetchFavorites = async () => {
  if (!authStore.isLoggedIn) return;
  try {
    const response = await axios.get('/api/user/favorites');
    if (response.data.success) {
      favoriteIds.value = response.data.data.data.map(p => p.id);
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách yêu thích:', error);
  }
};

const toggleFavorite = async (postId) => {
  if (!authStore.isLoggedIn) {
    alert('Vui lòng đăng nhập để sử dụng tính năng này');
    return;
  }
  try {
    const response = await axios.post(`/api/posts/${postId}/favorite`);
    if (response.data.success) {
      if (response.data.is_favorited) {
        favoriteIds.value.push(Number(postId));
      } else {
        favoriteIds.value = favoriteIds.value.filter(id => id !== Number(postId));
      }
    }
  } catch (error) {
    console.error('Lỗi khi thực hiện yêu thích:', error);
  }
};

// Hàm đệ quy tìm kiếm danh mục theo slug
const findCategoryRecursive = (cats, slug) => {
  for (const cat of cats) {
    if (cat.slug === slug) return cat;
    if (cat.children && cat.children.length > 0) {
      const found = findCategoryRecursive(cat.children, slug);
      if (found) return found;
    }
  }
  return null;
};

// Hàm đệ quy tìm kiếm danh mục cha theo id
const findParentCategory = (cats, childId) => {
  for (const cat of cats) {
    if (cat.children && cat.children.some(child => child.id === childId)) {
      return cat;
    }
    if (cat.children && cat.children.length > 0) {
      const found = findParentCategory(cat.children, childId);
      if (found) return found;
    }
  }
  return null;
};

// Load category attributes for filtering (only select-type with options)
const loadCategoryAttributes = async (catId) => {
  if (!catId) {
    categoryAttributes.value = [];
    selectedSpecs.value = {};
    showAttrDropdowns.value = {};
    return;
  }
  try {
    const res = await axios.get(`/api/categories/${catId}/attributes`);
    if (res.data.success) {
      // Chỉ lấy attribute có type = 'select' và có options
      categoryAttributes.value = res.data.data.filter(
        a => a.type === 'select' && a.options && a.options.length > 0
      );
    }
  } catch (error) {
    console.error('Lỗi khi tải thuộc tính danh mục:', error);
    categoryAttributes.value = [];
  }
};

// Load full provinces from local API (Exactly like PostCreate.vue)
const loadProvincesData = async () => {
  try {
    const response = await axios.get('/api/locations/provinces');
    allProvinces.value = response.data;
  } catch (error) {
    console.error('Lỗi khi tải danh sách tỉnh thành:', error);
  }
};

const loadCategoryData = async () => {
  loading.value = true;
  posts.value = [];
  subcategories.value = [];
  currentPage.value = 1;
  hasMore.value = true;

  try {
    const response = await axios.get('/api/categories');
    const allCats = response.data.data;
    allCategories.value = allCats; // save to ref

    if (route.path === '/marketplace' || !route.params.slug) {
      categoryName.value = 'Tất cả danh mục';
      categoryId.value = null;
      subcategories.value = [];
      categoryAttributes.value = [];
      selectedSpecs.value = {};
      await fetchCategoryPosts();
      loading.value = false;
      return;
    }

    const currentCat = findCategoryRecursive(allCats, route.params.slug);

    if (currentCat) {
      categoryName.value = currentCat.name;
      categoryId.value = currentCat.id;
      selectedSpecs.value = {}; // Reset khi đổi trang danh mục

      if (currentCat.children && currentCat.children.length > 0) {
        subcategories.value = currentCat.children;
      } else {
        const parent = findParentCategory(allCats, currentCat.id);
        if (parent) {
          subcategories.value = parent.children;
        } else {
          subcategories.value = [];
        }
      }

      // Load attributes đồng thời với posts
      await Promise.all([
        loadCategoryAttributes(currentCat.id),
        fetchCategoryPosts()
      ]);
    } else {
      categoryName.value = 'Không tìm thấy danh mục';
    }
  } catch (error) {
    console.error('Lỗi khi tải dữ liệu danh mục:', error);
    categoryName.value = 'Lỗi hệ thống';
  } finally {
    loading.value = false;
  }
};

const buildQueryUrl = (page) => {
  let url = `/api/posts?limit=8&page=${page}&sort=${activeSort.value}`;
  if (categoryId.value) {
    url += `&category_id=${categoryId.value}`;
  }
  if (activeProvince.value) {
    url += `&province_name=${encodeURIComponent(activeProvince.value)}`;
  }
  if (minPrice.value !== null) {
    url += `&price_min=${minPrice.value}`;
  }
  if (maxPrice.value !== null) {
    url += `&price_max=${maxPrice.value}`;
  }
  if (searchKeyword.value) {
    url += `&search=${encodeURIComponent(searchKeyword.value)}`;
  }
  // Thêm specs vào query
  Object.entries(selectedSpecs.value).forEach(([key, value]) => {
    if (value !== null && value !== '') {
      url += `&specs[${encodeURIComponent(key)}]=${encodeURIComponent(value)}`;
    }
  });
  return url;
};

const fetchCategoryPosts = async () => {
  try {
    const response = await axios.get(buildQueryUrl(1));
    if (response.data.success) {
      posts.value = response.data.data.data;
      currentPage.value = response.data.data.current_page;
      hasMore.value = response.data.data.current_page < response.data.data.last_page;
    }
  } catch (error) {
    console.error('Lỗi khi tải danh sách tin đăng:', error);
  }
};

const reloadPosts = async () => {
  loading.value = true;
  currentPage.value = 1;
  hasMore.value = true;
  posts.value = [];
  await fetchCategoryPosts();
  loading.value = false;
};

const loadMorePosts = async () => {
  if (loadingMore.value || !hasMore.value) return;
  loadingMore.value = true;
  try {
    const nextPage = currentPage.value + 1;
    const response = await axios.get(buildQueryUrl(nextPage));
    if (response.data.success) {
      posts.value = [...posts.value, ...response.data.data.data];
      currentPage.value = response.data.data.current_page;
      hasMore.value = response.data.data.current_page < response.data.data.last_page;
    }
  } catch (error) {
    console.error('Lỗi khi tải thêm tin đăng:', error);
  } finally {
    loadingMore.value = false;
  }
};

const collapsePosts = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    posts.value = posts.value.slice(0, currentPage.value * 8);
    hasMore.value = true;
  }
};

const getPrimaryImage = (post) => {
  if (post.images && post.images.length > 0) {
    const primary = post.images.find(img => img.is_primary);
    return primary ? primary.image_path : post.images[0].image_path;
  }
  return 'https://via.placeholder.com/400x300?text=No+Image';
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return 'Vừa xong';
  if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
  if (diff < 2592000) return `${Math.floor(diff / 86400)} ngày trước`;

  return date.toLocaleDateString('vi-VN');
};

// Global click handler to close dropdowns when clicking outside
const handleOutsideClick = (event) => {
  if (showPriceDropdown.value && !event.target.closest('.price-dropdown-wrapper')) {
    showPriceDropdown.value = false;
  }
  if (showSortDropdown.value && !event.target.closest('.sort-dropdown-wrapper')) {
    showSortDropdown.value = false;
  }
  if (showProvinceDropdown.value && !event.target.closest('.province-dropdown-wrapper')) {
    showProvinceDropdown.value = false;
  }
  if (showCategoryDropdown.value && !event.target.closest('.category-dropdown-wrapper')) {
    showCategoryDropdown.value = false;
  }
  // Đóng các attr dropdowns khi click bên ngoài
  Object.keys(showAttrDropdowns.value).forEach(key => {
    if (showAttrDropdowns.value[key] && !event.target.closest(`.attr-dropdown-${key}`)) {
      showAttrDropdowns.value[key] = false;
    }
  });
};

onMounted(() => {
  loadCategoryData();
  fetchFavorites();
  loadProvincesData();
  document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick);
});

watch(() => route.path, () => {
  // Only reload if we are on a category or mua-ban route
  if (route.path.startsWith('/category/') || route.path === '/marketplace') {
    loadCategoryData();
  }
});

watch(() => route.query.search, (newSearch) => {
  if (route.path.startsWith('/category/') || route.path === '/marketplace') {
    searchKeyword.value = newSearch || '';
    reloadPosts();
  }
});
</script>

<style scoped>
/* Isolation design system with !important to prevent any side effects of external css files */
.filter-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 0.25rem !important;
  padding: 0.45rem 1rem !important;
  background: #f2eff4 !important;
  border: 1px solid #c8c5d0 !important;
  border-radius: 9999px !important;
  font-size: 0.8rem !important;
  font-weight: 600 !important;
  color: #1c1b1f !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  outline: none !important;
  box-shadow: none !important;
}

.filter-pill:hover {
  background: #e6e1e9 !important;
  color: #1c1b1f !important;
  border-color: #777680 !important;
}

/* Perfect contrast active state for price/all inputs - No solid blue overlapping issues! */
.filter-pill.active-filter {
  background: var(--color-primary, #020037) !important;
  border-color: var(--color-primary, #020037) !important;
  color: #ffffff !important;
}

.filter-pill.active-filter * {
  color: #ffffff !important;
}

.filter-pill-dark {
  display: inline-flex !important;
  align-items: center !important;
  padding: 0.45rem 1rem !important;
  background: #1c1b1f !important;
  border-radius: 9999px !important;
  font-size: 0.8rem !important;
  font-weight: 700 !important;
  color: white !important;
  box-shadow: none !important;
}

.clear-cat-btn {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: rgba(255, 255, 255, 0.25) !important;
  color: white !important;
  border: none !important;
  border-radius: 50% !important;
  padding: 0px !important;
  width: 18px !important;
  height: 18px !important;
  font-size: 14px !important;
  line-height: 1 !important;
  margin-left: 0.4rem !important;
  cursor: pointer !important;
  transition: background-color 0.2s ease !important;
}

.clear-cat-btn:hover {
  background: rgba(255, 255, 255, 0.4) !important;
}

/* Scrollable location bar styling */
.loc-pill {
  display: inline-flex !important;
  align-items: center !important;
  padding: 0.4rem 0.9rem !important;
  border: 1px solid #c8c5d0 !important;
  border-radius: 9999px !important;
  font-size: 0.75rem !important;
  font-weight: 600 !important;
  color: #49454f !important;
  background: white !important;
  cursor: pointer !important;
  white-space: nowrap !important;
  transition: all 0.2s ease !important;
  outline: none !important;
  box-shadow: none !important;
}

.loc-pill:hover {
  background: #f7f3f8 !important;
  color: #49454f !important;
  border-color: #777680 !important;
}

/* FIX: active state text contrast solved perfectly! */
.loc-pill.active {
  border-color: var(--color-primary, #020037) !important;
  background: var(--color-primary, #020037) !important;
  color: #ffffff !important;
}

.loc-pill.active * {
  color: #ffffff !important;
}

/* Dynamic custom province select pill styling */
.province-option-btn {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 0.6rem 0.75rem;
  background: transparent;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-on-surface, #1c1b1f);
  transition: background-color 0.2s ease;
}

.province-option-btn:hover {
  background-color: var(--color-surface-container-high, #f2eff4);
}

.province-option-btn.active {
  background-color: var(--color-primary, #020037) !important;
  color: #ffffff !important;
}

/* Suggested tag design matching bottom block in image */
.suggest-tag-btn {
  display: inline-flex;
  align-items: center;
  padding: 0.55rem 1.25rem;
  background: white;
  border: 1px solid var(--color-outline-variant, #c8c5d0);
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-on-surface, #1c1b1f);
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s ease;
}

.suggest-tag-btn:hover {
  border-color: var(--color-primary, #020037);
  background: var(--color-primary-container, #e0e0ff);
  color: var(--color-primary, #020037);
  box-shadow: var(--shadow-xs);
}

/* Custom list layout scrollbars hiding */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Dropdown styling */
.price-range-btn {
  padding: 0.4rem !important;
  background: #ffffff !important;
  border: 1px solid #c8c5d0 !important;
  border-radius: 0.5rem !important;
  cursor: pointer !important;
  text-align: center !important;
  font-size: 0.75rem !important;
  font-weight: 600 !important;
  color: #1c1b1f !important;
  transition: all 0.2s ease !important;
}

.price-range-btn:hover {
  border-color: var(--color-primary, #020037) !important;
  background: var(--color-primary, #020037) !important;
  color: #ffffff !important;
}

.price-input {
  width: 100%;
  padding: 0.45rem 0.75rem;
  border: 1px solid var(--color-outline-variant, #c8c5d0);
  border-radius: 0.5rem;
  font-size: 0.75rem;
  color: var(--color-on-surface, #1c1b1f);
  outline: none;
  transition: border-color 0.2s ease;
}

.price-input:focus {
  border-color: var(--color-primary, #020037);
}

/* CUSTOM SORT OPTION DROP-DOWN BUTTONS (Matching second screenshot) */
.sort-option-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  background: transparent;
  border: none;
  cursor: pointer;
  text-align: left;
  transition: background-color 0.2s ease;
  color: var(--color-on-surface, #1c1b1f);
}

.sort-option-btn:hover {
  background-color: var(--color-surface-container-high, #f2eff4);
}

/* HORIZONTAL LIST CARD DESIGN - Premium clean style */
.post-list-card {
  display: flex;
  background: white;
  border: none;
  border-radius: 0;
  overflow: visible;
  padding: 1rem 1.25rem;
  gap: 1rem;
  transition: background-color 0.2s ease;
}

.post-list-card:hover {
  background-color: #f0f4ff;
}

.post-list-img-box {
  width: 9rem;
  height: 9rem;
  border-radius: 0.75rem;
  overflow: hidden;
  position: relative;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
}

.post-list-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}



.post-sold-badge {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  background: rgba(0, 0, 0, 0.65);
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  backdrop-filter: blur(4px);
}

.post-list-details {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.post-list-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-on-surface, #1c1b1f);
  line-clamp: 2;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.35;
  transition: color 0.2s ease;
  padding-right: 0.5rem;
}

.post-list-title:hover {
  color: var(--color-primary, #020037);
}

.post-list-price {
  font-size: 1.1rem;
  font-weight: 800;
  color: #ef4444;
  margin-top: 0.35rem;
}

.post-list-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.72rem;
  color: var(--color-on-surface-variant, #777680);
  margin-top: auto;
  padding-top: 0.4rem;
}

/* Pagination elements styling */
.btn-load-more {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 2rem;
  border: 2px solid var(--color-primary, #020037);
  background: transparent;
  color: var(--color-primary, #020037);
  font-weight: 700;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-load-more:hover:not(:disabled) {
  background: var(--color-primary, #020037);
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(2, 0, 55, 0.25);
  transform: translateY(-2px);
}

.btn-load-more:active:not(:disabled) {
  transform: translateY(0);
}

.btn-load-more:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-collapse {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 2rem;
  border: 2px solid var(--color-primary, #020037);
  background: transparent;
  color: var(--color-primary, #020037);
  font-weight: 700;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-collapse:hover {
  background: var(--color-primary, #020037);
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(2, 0, 55, 0.25);
  transform: translateY(-2px);
}

.btn-collapse:active {
  transform: translateY(0);
}
</style>

