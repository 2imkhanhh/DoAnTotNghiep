import { defineStore } from 'pinia';
import axios from 'axios';

export const useSidebarStore = defineStore('sidebar', {
    state: () => ({
        pendingPostsCount: null,
        pendingOrdersCount: null,
        pendingPurchasesCount: null,
        isFetchingAdmin: false,
        isFetchingSeller: false,
    }),
    
    actions: {
        async fetchAdminStats() {
            if (this.isFetchingAdmin) return;
            // Nếu đã có dữ liệu thì vẫn lấy lại dưới nền để cập nhật mới nhất
            this.isFetchingAdmin = true;
            try {
                const response = await axios.get('/api/admin/sidebar-stats');
                if (response.data.success) {
                    this.pendingPostsCount = response.data.data.pending_posts;
                    this.pendingPurchasesCount = response.data.data.pending_purchases;
                }
            } catch (error) {
                console.error('Lỗi khi lấy stats admin:', error);
            } finally {
                this.isFetchingAdmin = false;
            }
        },

        async fetchSellerStats() {
            if (this.isFetchingSeller) return;
            this.isFetchingSeller = true;
            try {
                const response = await axios.get('/api/seller/sidebar-stats');
                if (response.data.success) {
                    this.pendingOrdersCount = response.data.data.pending_orders;
                }
            } catch (error) {
                console.error('Lỗi khi lấy stats seller:', error);
            } finally {
                this.isFetchingSeller = false;
            }
        }
    }
});
