const fs = require('fs');
const path = require('path');

const files = [
  'resources/js/pages/admin/AdminUsers.vue',
  'resources/js/pages/admin/AdminPosts.vue',
  'resources/js/pages/admin/AdminBanners.vue',
  'resources/js/pages/admin/AdminCategoryAttributes.vue',
  'resources/js/pages/admin/AdminCategories.vue',
  'resources/js/pages/UserFavorites.vue',
  'resources/js/pages/ResetPassword.vue',
  'resources/js/pages/Register.vue',
  'resources/js/pages/PublicProfile.vue',
  'resources/js/pages/seller/SellerPosts.vue',
  'resources/js/pages/seller/PostEdit.vue',
  'resources/js/pages/seller/PostCreate.vue',
  'resources/js/pages/seller/SellerOrders.vue',
  'resources/js/pages/MyOrders.vue',
  'resources/js/pages/Chat.vue',
  'resources/js/pages/Login.vue',
  'resources/js/pages/PostDetail.vue',
  'resources/js/pages/Home.vue',
  'resources/js/pages/Checkout.vue'
];

files.forEach(f => {
  if (!fs.existsSync(f)) return;
  let content = fs.readFileSync(f, 'utf8');
  let original = content;

  // Add import if not exists and file uses alert/confirm
  if ((content.includes('alert(') || content.includes('confirm(')) && !content.includes('utils/alert')) {
    const depth = f.split('/').length - 3; // resources/js = 0
    let prefix = '../'.repeat(depth) || './';
    if (depth === 0) prefix = './'; // Actually resources/js/pages/Home.vue -> depth = 3-3 = 0. So prefix = './' ? No, it's relative to pages. Wait, utils is in resources/js/utils. pages is in resources/js/pages. So from pages/Home.vue it's `../utils/alert`. From pages/admin/AdminUsers.vue it's `../../utils/alert`.
    
    const numDirs = f.split('/').length - 2; // resources/js/pages/Home.vue -> 4 parts. 4-2 = 2? No.
    // resources/js is base.
    // Home.vue is in resources/js/pages -> relative to resources/js is 1 level deep.
    // AdminUsers.vue is in resources/js/pages/admin -> 2 levels deep.
    // So 1 level -> '../utils/alert'
    // 2 levels -> '../../utils/alert'
    const parts = f.split('resources/js/')[1].split('/'); // ['pages', 'Home.vue'] -> length 2.
    const relativeDepth = parts.length - 1; 
    let importPath = '../'.repeat(relativeDepth) + 'utils/alert';

    const importStmt = `import { toast, confirmDialog } from '${importPath}';\n`;
    content = content.replace('<script setup>', '<script setup>\n' + importStmt);
  }

  // Handle alert('X') -> toast('X', 'info') or 'error'/'success'
  // Let's do a smart regex:
  // alert('X')
  content = content.replace(/alert\(([^)]+)\)/g, (match, msg) => {
    // try to guess if it's error
    let type = 'info';
    if (msg.toLowerCase().includes('lỗi') || msg.toLowerCase().includes('không thể') || msg.toLowerCase().includes('error')) {
      type = 'error';
    } else if (msg.toLowerCase().includes('thành công') || msg.toLowerCase().includes('success')) {
      type = 'success';
    }
    return `toast(${msg}, '${type}')`;
  });

  // Handle confirm('X') -> await confirmDialog('X')
  content = content.replace(/confirm\(([^)]+)\)/g, `await confirmDialog($1)`);

  if (content !== original) {
    fs.writeFileSync(f, content, 'utf8');
    console.log('Updated ' + f);
  }
});
