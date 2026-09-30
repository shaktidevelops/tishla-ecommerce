document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const nav = document.querySelector('[data-nav]');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      toggle.textContent = open ? '×' : '☰';
    });
  }
  const revealItems = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealItems.length) {
    const observer = new IntersectionObserver((entries, io) => {
      entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); } });
    }, { threshold: 0.08 });
    revealItems.forEach(item => observer.observe(item));
  } else revealItems.forEach(item => item.classList.add('is-visible'));
  const mainImage = document.querySelector('[data-main-image]');
  const thumbs = document.querySelectorAll('[data-product-thumb]');
  if (mainImage && thumbs.length) thumbs.forEach(thumb => thumb.addEventListener('click', () => { mainImage.src = thumb.dataset.image; mainImage.alt = thumb.dataset.alt || ''; thumbs.forEach(item => item.classList.remove('active')); thumb.classList.add('active'); }));

  document.querySelectorAll('[data-add-variant]').forEach(button => button.addEventListener('click', () => {
    const editor = document.querySelector('[data-variant-editor]'); if (!editor) return;
    editor.querySelector('[data-variant-empty]')?.remove();
    const index = editor.querySelectorAll('[data-variant-row]').length;
    const row = document.createElement('div'); row.className='variant-row'; row.dataset.variantRow='';
    row.innerHTML=`<label class="field compact"><span>Variant</span><input name="variants[${index}][name]" placeholder="M · Wine"></label><label class="field compact"><span>Size</span><input name="variants[${index}][size_name]" placeholder="M"></label><label class="field compact"><span>Colour</span><input name="variants[${index}][color_name]" placeholder="Wine"></label><label class="field compact"><span>HEX</span><input name="variants[${index}][color_hex]" placeholder="#6E102B"></label><label class="field compact"><span>SKU</span><input name="variants[${index}][sku]" placeholder="TSH-V-${index+1}"></label><label class="field compact"><span>Price</span><input type="number" step="0.01" name="variants[${index}][price]" placeholder="Optional"></label><label class="field compact"><span>Compare at</span><input type="number" step="0.01" name="variants[${index}][compare_at_price]" placeholder="Optional"></label><label class="toggle compact"><input type="checkbox" name="variants[${index}][is_active]" value="1" checked> Active</label><button type="button" class="variant-remove" data-remove-variant aria-label="Remove variant">×</button>`;
    editor.appendChild(row);
  }));
  document.addEventListener('click', event => {
    const remove=event.target.closest('[data-remove-variant]'); if(remove){ remove.closest('[data-variant-row]')?.remove(); const editor=document.querySelector('[data-variant-editor]'); if(editor && !editor.querySelector('[data-variant-row]')) editor.innerHTML='<div class="variant-empty" data-variant-empty><span>＋</span><strong>No variants yet.</strong><small>Add sizes, colours or other purchasable combinations.</small></div>'; }
    const stock=event.target.closest('[data-stock-open]');
    if(stock){
      const data=JSON.parse(stock.dataset.stockOpen);
      const form=document.querySelector('[data-stock-form]');
      if(form){ form.querySelector('[name="variant_id"]').value=data.variant_id; form.querySelector('[name="location_id"]').value=data.location_id; form.querySelector('[name="quantity"]').focus(); form.scrollIntoView({behavior:'smooth',block:'center'}); }
    }
  });
  const adminToggle=document.querySelector('[data-admin-menu]'); const adminSidebar=document.querySelector('[data-admin-sidebar]');
  if(adminToggle && adminSidebar) adminToggle.addEventListener('click',()=>adminSidebar.classList.toggle('open'));
});
