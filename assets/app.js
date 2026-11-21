(function(){
const toggle=document.querySelector('#menu-trigger'),side=document.querySelector('#sidebar');
if(toggle&&side)toggle.addEventListener('click',()=>side.classList.toggle('open'));
document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('submit',ev=>{if(!confirm(el.dataset.confirm||'Lanjutkan?'))ev.preventDefault()}));
const passwordToggle=document.querySelectorAll('[data-toggle-password]');passwordToggle.forEach(btn=>btn.addEventListener('click',()=>{const input=document.getElementById(btn.dataset.togglePassword);if(input)input.type=input.type==='password'?'text':'password'}));
})();
