<?php
get_header();

$code = get_query_var('taiapp_code'); // Lấy mã từ URL
?>
<style>
*, *::after, *::before {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.wrapper {
    width: 100%;
    height: 100%;
    min-height: 100vh;
    min-width: 100vw;
    overflow: hidden;
}

.section2 {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    max-width: 700px;
    margin: 0 auto;
}

.img {
    max-width: 750px;
    width: 100%
}

</style>


<section class="section2">
            <div class="main-container">
               <div class="main-img">
                    <img id="img-44" src="https://api.gsmilktea.vn/logo.jpeg" alt="999bet" width="200px" height="200px">
               </div>
               <div class="g_down">
                <div class="wrap">
                   

                    <h1>Tải app với mã:  <p id="copy-text"><?php echo esc_html($code); ?></p></h1>
                    <p>Link tải dành riêng cho bạn: 
                    
                     <a class="log_in download"   id="copy-button">
                         
                     </a>
                     </p>
                </div>
                   
  
                </div>
            </div>
          
        </section>
        
        
        <script>
            function getMobileOS() {
  
                const userAgent = navigator.userAgent || navigator.vendor || window.opera;
            
                if (/android/i.test(userAgent)) {
                    return "Android";
                }
            
                if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                    return "iOS";
                }
            
                return "Unknown";
            }
    const appScheme = "gsmilktea://taiapp/84935463001"; // Deep link
    const appStoreURL = "https://apps.apple.com/vn/app/gs-milk-tea/id6739470016?l=vi";
    const playStoreURL = "https://play.google.com/store/apps/details?id=vn.gsmilktea.app";

    const deviceOS = getMobileOS();
    
                 const now = Date.now();
    if (deviceOS === "Android") {
    
    document.getElementById("copy-button").innerHTML = '<br><img data-content="android_box" id="img-logo" src="https://e7.pngegg.com/pngimages/542/263/png-clipart-google-play-logo-google-play-android-app-store-google-play-text-logo.png" style=" width:160px;">';

    } else if (deviceOS === "iOS") {
       document.getElementById("copy-button").innerHTML = '<br><img data-content="android_box"  src="https://brandeps.com/logo-download/D/Download-on-the-App-Store-logo-01.png" style=" width:160px;">';
    
    }else{
        document.getElementById("copy-button").innerHTML = '<br><img data-content="android_box" id="img-logo" src="https://e7.pngegg.com/pngimages/542/263/png-clipart-google-play-logo-google-play-android-app-store-google-play-text-logo.png" style=" width:160px;"><br><img data-content="android_box"  src="https://brandeps.com/logo-download/D/Download-on-the-App-Store-logo-01.png" style=" width:160px;">';
    }


  

            
            document.getElementById("copy-button").addEventListener("click", function() {
    const text = document.getElementById("copy-text").innerText;
                    window.location = appScheme;

    navigator.clipboard.writeText(text)
        .then(() => {

            setTimeout(() => {
              const timeSpent = Date.now() - now;
              if (timeSpent < 2000) {
              
                if (deviceOS === "Android") {
                    alert("Thiết bị là Android");
                    window.location = playStoreURL;
        
                    // window.location.href = "https://play.google.com/store/apps/details?id=your.app";
                } else if (deviceOS === "iOS") {
                    alert("Thiết bị là iOS");
                    window.location = appStoreURL;
        
                    // window.location.href = "https://apps.apple.com/app/id123456789";
                } else {
                    console.log("Không xác định được thiết bị");
                }
              }
            }, 1500);
        })
        .catch(err => {
            console.error("Lỗi khi copy: ", err);
        });
});
        </script>

<?php
get_footer();
