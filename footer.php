  </div> <!-- end layout -->
  <script>
    const btn=document.getElementById('toggleSidebar');
    const sb=document.getElementById('sb');
    if(btn && sb){
      btn.addEventListener('click',()=>sb.classList.toggle('open'));
    }

    const logout=document.querySelector('a[href="#logout"]');
    if(logout){
      logout.addEventListener('click',e=>{
        e.preventDefault();
        if(confirm("Yakin ingin logout dari Dashboard PPI PHBW?")){
          localStorage.removeItem('ppi_logged_in');
          window.location.href="/login.php";
        }
      });
    }
  </script>
</body>
</html>
