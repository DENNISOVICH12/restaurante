// js/enviar.js
document.addEventListener('DOMContentLoaded', () => {
  // ====== Selectores ======
  const carritoTbody   = document.querySelector('#carrito tbody');
  const addButtons     = document.querySelectorAll('.agregar-carrito');
  const vaciarBtn      = document.querySelector('#vaciar-carrito');
  const modal          = document.querySelector('#modal-cliente');
  const closeModalBtn  = document.querySelector('#cerrar-modal') || document.querySelector('.close');
  const formCliente    = document.querySelector('#form-cliente');
  const imgCarrito     = document.querySelector('#img-carrito');

  // ====== Estado ======
  const KEY = 'carrito';
  const COP = new Intl.NumberFormat('es-CO');
  const money = (n) => `${COP.format(+n || 0)} COP`;
  const safeParse = (v) => { try { return JSON.parse(v || '[]'); } catch { return []; } };

  let items = safeParse(localStorage.getItem(KEY));
  const save = () => localStorage.setItem(KEY, JSON.stringify(items));

  // ====== Badge en icono carrito ======
  function renderBadge() {
    if (!imgCarrito) return;
    const parent = imgCarrito.parentElement;
    if (!parent) return;
    let badge = parent.querySelector('.carrito-cantidad');
    if (badge) badge.remove();
    if (items.length > 0) {
      badge = document.createElement('span');
      badge.className = 'carrito-cantidad';
      Object.assign(badge.style, {
        position:'absolute', top:'-8px', right:'-8px', backgroundColor:'red', color:'white',
        borderRadius:'50%', width:'20px', height:'20px', display:'flex',
        justifyContent:'center', alignItems:'center', fontSize:'12px'
      });
      badge.textContent = items.length;
      parent.style.position = 'relative';
      parent.appendChild(badge);
    }
  }

  // ====== Escapar HTML ======
  const escapeHtml = (s='') =>
    s.replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  // ====== Render del carrito (5 columnas exactas con Total) ======
  function render() {
    if (!carritoTbody) return;
    carritoTbody.innerHTML = '';

    let total = 0;

    items.forEach((it, i) => {
      const cant   = Math.max(1, +it.cantidad || 1);
      const precio = +it.precio || 0;
      total += precio * cant;

      const tr = document.createElement('tr');
      tr.dataset.index = i;
      tr.innerHTML = `
        <td><img src="${it.imagen || ''}" width="50" height="50" style="object-fit:cover;border-radius:6px"></td>
        <td>${escapeHtml(it.nombre || 'Producto')}</td>
        <td>${money(precio)}</td>
        <td>
          <input type="number" min="1" class="qty" value="${cant}" style="width:64px;padding:4px;border-radius:6px;border:1px solid #ddd">
        </td>
        <td>
          <a href="#" class="desc" title="Instrucciones">✏️</a>
          &nbsp;&nbsp;
          <a href="#" class="del" title="Quitar">X</a>
        </td>
      `;
      carritoTbody.appendChild(tr);
    });

    const trTotal = document.createElement('tr');
    trTotal.innerHTML = `
      <td colspan="3"><strong>Total</strong></td>
      <td colspan="2"><strong>${money(total)}</strong></td>
    `;
    carritoTbody.appendChild(trTotal);

    renderBadge();
  }

  // ====== Delegación en el <tbody> (acciones y cantidad) ======
  if (carritoTbody) {
    carritoTbody.addEventListener('click', (e) => {
      const a = e.target.closest('a');
      if (!a) return;

      const tr = e.target.closest('tr');
      const idx = +tr?.dataset?.index;
      if (Number.isNaN(idx)) return;

      if (a.classList.contains('del')) {
        e.preventDefault();
        items.splice(idx, 1);
        save(); render();
      }

      if (a.classList.contains('desc')) {
        e.preventDefault();
        const val = prompt('Instrucciones especiales:', items[idx].descripcion || '');
        if (val !== null) { items[idx].descripcion = val; save(); render(); }
      }
    });

    carritoTbody.addEventListener('change', (e) => {
      const qty = e.target.closest('.qty');
      if (!qty) return;
      const tr = e.target.closest('tr');
      const idx = +tr?.dataset?.index;
      if (Number.isNaN(idx)) return;
      items[idx].cantidad = Math.max(1, parseInt(qty.value, 10) || 1);
      save(); render();
    });
  }

  // ====== Agregar productos ======
  addButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const card = e.currentTarget.closest('.product, .ofert-1');
      if (!card) return;

      const nombre = card.querySelector('h3')?.textContent?.trim() || 'Producto';
      const precioText = card.querySelector('.precio')?.textContent || '0';
      const precio = parseInt(precioText.replace(/[^\d]/g,''), 10) || 0;
      const imagen = card.querySelector('img')?.getAttribute('src') || '';

      const exist = items.find(it => (it.nombre || '').toLowerCase() === nombre.toLowerCase());
      if (exist) {
        exist.cantidad = (+exist.cantidad || 1) + 1;
      } else {
        items.push({ nombre, precio, imagen, cantidad: 1, descripcion: '' });
      }
      save(); render();

      toast(`¡${nombre} agregado al carrito!`);
    });
  });

  // ====== Vaciar ======
  if (vaciarBtn) {
    vaciarBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (!items.length) return;
      if (!confirm('¿Vaciar el carrito?')) return;
      items = []; save(); render();
    });
  }

  // ====== Abrir modal por DELEGACIÓN (evita problemas con hover del carrito) ======
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#btn-confirmar');
    if (!btn) return;
    e.preventDefault();
    if (!items.length) { alert('El carrito está vacío.'); return; }
    if (modal) modal.style.display = 'block';
  });

  // ====== Cerrar modal ======
  if (closeModalBtn && modal) {
    closeModalBtn.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
  }

  // ====== Enviar pedido (robusto) ======
  if (formCliente) {
    formCliente.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!items.length) return alert('El carrito está vacío.');

      const nombre    = document.querySelector('#nombre')?.value?.trim();
      const telefono  = document.querySelector('#telefono')?.value?.trim();
      const direccion = document.querySelector('#direccion')?.value?.trim();
      if (!nombre || !telefono) return alert('Complete nombre y teléfono.');

      const btn = formCliente.querySelector('button[type="submit"]');
      const old = btn?.textContent;
      if (btn) { btn.textContent = 'Enviando…'; btn.disabled = true; }

      try {
        const res = await fetch('guardar_pedido.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ nombre, telefono, direccion, pedido: items })
        });

        if (!res.ok) {
          const errText = await res.text();
          throw new Error(`HTTP ${res.status} ${res.statusText}:\n${errText}`);
        }

        const ct = (res.headers.get('content-type') || '').toLowerCase();
        if (!ct.includes('application/json')) {
          const raw = await res.text();
          throw new Error(`Respuesta no JSON del servidor:\n${raw}`);
        }

        const data = await res.json();
        if (data.status !== 'success') {
          throw new Error(data.message || 'No se pudo guardar el pedido');
        }

        alert(`Pedido #${data.id_pedido} enviado correctamente.`);
        items = []; save(); render(); formCliente.reset();
        if (modal) modal.style.display = 'none';

      } catch (e2) {
        try { console.error(e2); } catch (_) {}
        alert('Error al enviar el pedido.\n' + (e2?.message || 'Intente nuevamente.'));
      } finally {
        if (btn) { btn.textContent = old; btn.disabled = false; }
      }
    });
  }

  // ====== Sincronizar entre pestañas ======
  window.addEventListener('storage', (e) => {
    if (e.key === KEY) { items = safeParse(e.newValue); render(); }
  });

  // ====== Toast ======
  function toast(msg) {
    const el = document.createElement('div');
    el.textContent = msg;
    Object.assign(el.style, {
      position:'fixed', left:'50%', bottom:'24px', transform:'translateX(-50%)',
      background:'#111', color:'#fff', padding:'10px 14px', borderRadius:'8px',
      boxShadow:'0 8px 20px rgba(0,0,0,.25)', zIndex:'9999'
    });
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 1600);
  }

  // ====== Primer render ======
  render();
});
