<div id="chacha-widget" class="fixed bottom-5 right-5 z-50 w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-3xl border bg-white shadow-2xl">
    <button id="chacha-toggle" type="button" class="flex w-full items-center justify-between bg-slate-950 px-5 py-4 text-left font-bold text-white"><span>✦ Chacha</span><span>AI Shopping Assistant</span></button>
    <div id="chacha-panel" class="hidden">
        <div id="chacha-messages" class="h-80 space-y-3 overflow-y-auto p-4 text-sm"><div class="max-w-[85%] rounded-2xl bg-slate-100 p-3">Hi! I'm Chacha. I can help with products, orders, shipping and returns.</div></div>
        <form id="chacha-form" class="flex gap-2 border-t p-3"><input id="chacha-input" required maxlength="4000" placeholder="Ask Chacha..." class="min-w-0 flex-1 rounded-xl border px-3 py-2"><button class="rounded-xl bg-slate-950 px-4 font-semibold text-white">Send</button></form>
    </div>
</div>
<script>
(()=>{const t=document.getElementById('chacha-toggle'),p=document.getElementById('chacha-panel'),f=document.getElementById('chacha-form'),i=document.getElementById('chacha-input'),m=document.getElementById('chacha-messages');t.onclick=()=>p.classList.toggle('hidden');f.onsubmit=async e=>{e.preventDefault();const q=i.value.trim();if(!q)return;m.insertAdjacentHTML('beforeend',`<div class="ml-auto max-w-[85%] rounded-2xl bg-slate-950 p-3 text-white">${q.replace(/</g,'&lt;')}</div>`);i.value='';const r=await fetch('/chacha/ask',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||''},body:JSON.stringify({message:q})});const d=await r.json();m.insertAdjacentHTML('beforeend',`<div class="max-w-[85%] rounded-2xl bg-slate-100 p-3">${String(d.message||'Please contact support.').replace(/</g,'&lt;')}</div>`);m.scrollTop=m.scrollHeight;};})();
</script>
