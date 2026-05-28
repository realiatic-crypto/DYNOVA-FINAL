/* DYNOVA NETWORK – small JS helpers */
(function(){
  // Copy to clipboard
  document.querySelectorAll('[data-copy]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const txt = btn.getAttribute('data-copy');
      const fallback = ()=>{
        const ta=document.createElement('textarea');ta.value=txt;document.body.appendChild(ta);ta.select();
        try{document.execCommand('copy');}catch(e){}ta.remove();
      };
      if(navigator.clipboard){
        navigator.clipboard.writeText(txt).catch(fallback);
      }else fallback();
      const t = document.getElementById('copyToast');
      if(t){t.classList.add('show');setTimeout(()=>t.classList.remove('show'),1400);}
    });
  });

  // Period toggle (preserves query)
  // handled via real links – nothing extra here

  // Star rating: show selected
  document.querySelectorAll('.stars').forEach(s=>{
    const inputs = s.querySelectorAll('input');
    inputs.forEach(inp=>{
      inp.addEventListener('change', ()=>{
        s.dataset.value = inp.value;
      });
    });
  });

  // Embed YouTube videos
  document.querySelectorAll('[data-yt]').forEach(box=>{
    const url = box.getAttribute('data-yt');
    const m = url.match(/(?:v=|youtu\.be\/|embed\/)([\w-]{6,})/);
    if(m){
      const id = m[1];
      box.innerHTML = '<iframe src="https://www.youtube.com/embed/'+id+'?rel=0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
    }
  });

  // Bottom-nav active pulse on tap
  document.querySelectorAll('.nav-bottom a').forEach(a=>{
    a.addEventListener('click', ()=>{
      a.style.transform='scale(.92)';
      setTimeout(()=>a.style.transform='',150);
    });
  });
})();
