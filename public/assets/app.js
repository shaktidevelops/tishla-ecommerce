document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const navToggle = document.querySelector('[data-menu-toggle]');
  const nav = document.querySelector('[data-nav]');

  navToggle?.addEventListener('click', () => {
    const open = nav?.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    navToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
  });

  const searchPanel = document.querySelector('[data-search-panel]');
  const searchInput = document.querySelector('[data-global-search]');
  const openSearch = () => {
    if (!searchPanel) return;
    searchPanel.classList.add('open');
    searchPanel.setAttribute('aria-hidden', 'false');
    body.classList.add('overlay-open');
    setTimeout(() => searchInput?.focus(), 80);
  };
  const closeSearch = () => {
    if (!searchPanel) return;
    searchPanel.classList.remove('open');
    searchPanel.setAttribute('aria-hidden', 'true');
    body.classList.remove('overlay-open');
  };
  document.querySelectorAll('[data-search-open]').forEach(button => button.addEventListener('click', openSearch));
  document.querySelectorAll('[data-search-close]').forEach(button => button.addEventListener('click', closeSearch));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeSearch();
  });

  const filterPanel = document.querySelector('[data-filter-panel]');
  document.querySelector('[data-filter-open]')?.addEventListener('click', () => filterPanel?.classList.add('open'));
  document.querySelector('[data-filter-close]')?.addEventListener('click', () => filterPanel?.classList.remove('open'));

  const revealItems = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealItems.length) {
    const observer = new IntersectionObserver((entries, io) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });
    revealItems.forEach(item => observer.observe(item));
  } else {
    revealItems.forEach(item => item.classList.add('is-visible'));
  }

  const mainImage = document.querySelector('[data-main-image]');
  document.querySelectorAll('[data-product-thumb]').forEach(thumb => thumb.addEventListener('click', () => {
    if (!mainImage) return;
    mainImage.src = thumb.dataset.image;
    mainImage.alt = thumb.dataset.alt || '';
    document.querySelectorAll('[data-product-thumb]').forEach(item => item.classList.remove('active'));
    thumb.classList.add('active');
  }));

  const productPrice = document.querySelector('[data-product-price]');
  const comparePrice = document.querySelector('[data-compare-price]');
  document.querySelectorAll('input[name="variant_id"][data-price]').forEach(input => input.addEventListener('change', () => {
    const price = Number(input.dataset.price || 0);
    const compare = Number(input.dataset.compare || 0);
    if (productPrice) productPrice.firstChild.textContent = '₹' + Math.round(price).toLocaleString('en-IN');
    if (comparePrice) {
      if (compare > price) {
        comparePrice.hidden = false;
        comparePrice.textContent = '₹' + Math.round(compare).toLocaleString('en-IN');
      } else {
        comparePrice.hidden = true;
      }
    }
  }));

  document.querySelectorAll('[data-remove-variant]').forEach(button => button.addEventListener('click', () => {
    button.closest('[data-variant-row]')?.remove();
  }));

  const adminToggle = document.querySelector('[data-admin-menu]');
  const adminSidebar = document.querySelector('[data-admin-sidebar]');
  if (adminToggle && adminSidebar) adminToggle.addEventListener('click', () => {
    adminSidebar.classList.toggle('open');
    body.classList.toggle('admin-menu-open', adminSidebar.classList.contains('open'));
  });

  const selectAll = document.querySelector('[data-select-all]');
  const rowSelects = [...document.querySelectorAll('[data-row-select]')];
  const selectedCount = document.querySelector('[data-selected-count]');
  const deleteButton = document.querySelector('[data-bulk-delete]');
  const refreshBulk = () => {
    const count = rowSelects.filter(input => input.checked).length;
    if (selectedCount) selectedCount.textContent = count + ' selected';
    if (deleteButton) deleteButton.disabled = count === 0;
    if (selectAll) selectAll.checked = count > 0 && count === rowSelects.length;
  };
  if (selectAll) selectAll.addEventListener('change', () => {
    rowSelects.forEach(input => { input.checked = selectAll.checked; });
    refreshBulk();
  });
  rowSelects.forEach(input => input.addEventListener('change', refreshBulk));
  const deleteForm = document.querySelector('[data-bulk-delete-form]');
  deleteForm?.addEventListener('submit', event => {
    const count = rowSelects.filter(input => input.checked).length;
    if (!count || !window.confirm('Delete ' + count + ' selected product(s)? Their variants, inventory records and catalogue relationships will also be removed.')) event.preventDefault();
  });
  refreshBulk();
});
