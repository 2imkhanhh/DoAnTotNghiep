import Swal from 'sweetalert2';

// Cấu hình chung cho Toast
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    customClass: {
        popup: '!rounded-xl !shadow-xl !bg-surface-container-lowest !border !border-outline-variant/30 !flex !items-center !px-4 !py-3',
        title: '!text-sm !font-semibold !text-on-surface !m-0 !ml-2 !self-center !pt-0',
        timerProgressBar: '!bg-primary',
        icon: '!border-0 !m-0 !flex !items-center !justify-center !w-auto !h-auto !self-center'
    },
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

/**
 * Hiển thị thông báo Toast (Tự ẩn sau vài giây)
 * @param {string} title Nội dung thông báo
 * @param {string} icon 'success', 'error', 'warning', 'info'
 */
export const toast = (title, icon = 'success') => {
    let iconName = 'check_circle';
    let iconColor = 'text-green-500';
    if (icon === 'error') { iconName = 'error'; iconColor = 'text-red-500'; }
    if (icon === 'warning') { iconName = 'warning'; iconColor = 'text-amber-500'; }
    if (icon === 'info') { iconName = 'info'; iconColor = 'text-blue-500'; }

    return Toast.fire({
        title: title,
        iconHtml: `<span class="material-symbols-outlined text-[24px] ${iconColor} animate-in zoom-in duration-200">${iconName}</span>`
    });
};

/**
 * Hiển thị hộp thoại hỏi đáp (Đồng ý/Hủy)
 * @param {string} title Tiêu đề câu hỏi
 * @param {string} text Chi tiết câu hỏi (tùy chọn)
 * @param {string} confirmButtonText Chữ nút đồng ý
 * @param {string} cancelButtonText Chữ nút hủy
 * @returns {Promise<boolean>}
 */
export const confirmDialog = (title, text = '', confirmButtonText = 'Đồng ý', cancelButtonText = 'Hủy') => {
    let swalTitle = title;
    let swalText = text;

    // Nếu không truyền text mà truyền câu hỏi dài vào title (do tool tự động thay thế)
    if (!text && title.length > 20) {
        swalTitle = 'Xác nhận';
        swalText = title;
    }

    return Swal.fire({
        title: swalTitle,
        html: swalText,
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        backdrop: 'rgba(0,0,0,0.5)',
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
            popup: '!rounded-2xl !shadow-2xl !bg-surface-container-lowest !p-6 !max-w-sm animate-in fade-in zoom-in duration-200',
            title: '!text-lg !font-bold !text-on-surface !p-0 !mt-4 !mb-0',
            htmlContainer: '!text-sm !text-on-surface-variant !m-0 !mt-1 !mb-6 !leading-relaxed',
            confirmButton: 'bg-primary text-on-primary hover:bg-primary/90 rounded-lg px-6 py-2.5 text-sm font-semibold shadow-md transition-all active:scale-95 flex-1 !cursor-pointer',
            cancelButton: 'bg-surface-container-high text-on-surface hover:bg-surface-container-highest border border-outline-variant rounded-lg px-6 py-2.5 text-sm font-semibold shadow-sm transition-all active:scale-95 flex-1 !cursor-pointer',
            actions: '!mt-0 !gap-3 !w-full !flex',
            icon: '!border-0 !m-0 !mx-auto !flex !items-center !justify-center !w-16 !h-16 !rounded-full !bg-amber-100'
        },
        iconHtml: `<span class="material-symbols-outlined text-[32px] text-amber-600">warning</span>`
    }).then((result) => {
        return result.isConfirmed;
    });
};

/**
 * Hiển thị hộp thoại thông báo có nút OK ở giữa màn hình
 */
export const alertDialog = (title, text = '', icon = 'info') => {
    let iconName = 'info';
    let iconColor = 'text-blue-600';
    let iconBg = 'bg-blue-100';

    if (icon === 'success') { iconName = 'check_circle'; iconColor = 'text-green-600'; iconBg = 'bg-green-100'; }
    if (icon === 'error') { iconName = 'error'; iconColor = 'text-red-600'; iconBg = 'bg-red-100'; }
    if (icon === 'warning') { iconName = 'warning'; iconColor = 'text-amber-600'; iconBg = 'bg-amber-100'; }

    let swalTitle = title;
    let swalText = text;
    if (!text && title.length > 20) {
        swalTitle = 'Thông báo';
        swalText = title;
    }

    return Swal.fire({
        title: swalTitle,
        html: swalText,
        confirmButtonText: 'OK',
        backdrop: 'rgba(0,0,0,0.5)',
        buttonsStyling: false,
        customClass: {
            popup: '!rounded-2xl !shadow-2xl !bg-surface-container-lowest !p-6 !max-w-sm animate-in fade-in zoom-in duration-200',
            title: '!text-lg !font-bold !text-on-surface !p-0 !mt-4 !mb-0',
            htmlContainer: '!text-sm !text-on-surface-variant !m-0 !mt-1 !mb-6 !leading-relaxed',
            confirmButton: 'bg-primary text-on-primary hover:bg-primary/90 rounded-lg px-6 py-2.5 text-sm font-semibold shadow-md transition-all active:scale-95 w-full !cursor-pointer',
            actions: '!mt-0 !w-full',
            icon: `!border-0 !m-0 !mx-auto !flex !items-center !justify-center !w-16 !h-16 !rounded-full ${iconBg}`
        },
        iconHtml: `<span class="material-symbols-outlined text-[32px] ${iconColor}">${iconName}</span>`
    });
};
