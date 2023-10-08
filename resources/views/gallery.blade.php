@if(!empty($gallery))
  @push('styles')
    <style>
      .gallerySlider swiper-slide {
        text-align: center;
        font-size: 18px;
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .gallerySlider swiper-slide img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      /* CSS for the modal */
      .gallery_modal {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.9);
          z-index: 1;
          overflow: hidden;
      }

      .gallery_modal_content {
          display: block;
          max-width: 100%;
          max-height: 100%;
          margin: 0 auto;
      }

      .gallery_close {
          color: white;
          position: absolute;
          top: 10px;
          right: 15px;
          font-size: 30px;
          cursor: pointer;
      }

      .gallery_close:hover {
          color: red;
      }

    </style>   
  @endpush
  <section class="py-6">
    <h2 class="text-3xl font-bold sm:text-4xl text-center my-5">Gallery</h2>
      <div class="h-full">
        <swiper-container class="gallerySlider" pagination="true" pagination-clickable="true" space-between="30" slides-per-view="3" navigation="true">
          @foreach($gallery as $key => $pic)
            <swiper-slide class="relative group h-96 ">
              <img src="{{asset('storage/'.$pic)}}" alt="{{$key}}" class="object-cover rounded">
              <label for="{{Str::camel($key)}}_img_modal" class="opacity-0 absolute inset-0 flex justify-center items-center transition duration-300 bg-black bg-opacity-60 group-hover:opacity-100 cursor-pointer" onclick="openModal('{{asset('storage/'.$pic)}}')">
                <div class="text-white transition-transform transform scale-0 group-hover:scale-100">
                    View
                </div>
              </label>
            </swiper-slide>
          @endforeach
        </swiper-container>

        <!-- Modal -->
        <div id="GlrModal" class="gallery_modal z-[100]">
            <span class="gallery_close" onclick="closeModal()">&times;</span>
            <div class="h-full w-full flex justify-center items-center py-5">
              <img class="gallery_modal_content" id="modalPic">
            </div>
        </div>
      </div>
    </div>
    @push('scripts')
      <script>
          function openModal(imageSrc) {
              const modal = document.getElementById("GlrModal");
              const modalImg = document.getElementById("modalPic");
              
              modal.style.display = "block";
              modalImg.src = imageSrc;
              
              // Set the modal to be full-screen based on the screen size
              modalImg.style.maxWidth = "100%";
              modalImg.style.maxHeight = "100%";
              
              document.body.style.overflow = "hidden"; // Disable scrolling when modal is open
          }

          function closeModal() {
              const modal = document.getElementById("GlrModal");
              modal.style.display = "none";
              document.body.style.overflowY = "auto"; // Re-enable scrolling when modal is closed
          }

      </script>
    @endpush
  </section>
@endif