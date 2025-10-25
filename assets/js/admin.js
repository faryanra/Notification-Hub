// Tabs + Safe redirect tab persistence
document.addEventListener('DOMContentLoaded', function(){

  // --- Tabs ---
  const tabs = document.querySelectorAll('.nav-tab');
  const panes = document.querySelectorAll('.nh-tab');
  tabs.forEach(t => t.addEventListener('click', e => {
    e.preventDefault();
    tabs.forEach(x=>x.classList.remove('nav-tab-active'));
    t.classList.add('nav-tab-active');
    panes.forEach(p=>p.style.display='none');
    const href = t.getAttribute('href');
    const id = href.replace('?page=nh_settings&tab=','nh-tab-');
    document.getElementById(id).style.display = 'block';
    history.replaceState(null, '', href);
  }));

  // --- Safe test buttons (attach current tab before redirect) ---
  document.querySelectorAll('.nh-test-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
      e.preventDefault();
      const tab = btn.dataset.tab || 'general';
      const href = new URL(btn.href);
      href.searchParams.set('tab', tab);
      window.location.href = href.toString();
    });
  });
});
