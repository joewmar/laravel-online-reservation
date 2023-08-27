<footer class="footer p-10 bg-base-200 text-base-content">
    <div>
      <img src="{{asset('images/logo.png')}}" width="50" height="50" alt="AA Logo">
      <p>Alvin and Angie Mt. Pinatubo Accommodation and Tours<br/>Providing room and tour service since 2009</p>
    </div> 
    <div>
      <span class="footer-title">Social</span> 
      <div class="grid grid-flow-col gap-5">
        <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{env('MAIN_CONTACT_EMAIL', '#')}}">
          <i class="fa-solid fa-envelope text-3xl"></i>
        </a>
        <a href="{{env('MAIN_FACEBOOK_LINK', '#')}}">
          <i class="fa-brands fa-facebook text-3xl"></i>
        </a>
        <a href="https://wa.me/{{env('MAIN_WHATSAPP_NUMBER', '00000000000')}}">
          <i class="fa-brands fa-whatsapp text-3xl font-bold"></i>
        </a> 
      </div>
    </div>
  </footer>