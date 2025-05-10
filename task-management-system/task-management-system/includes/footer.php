</div>
    <!-- Footer content -->
    <footer class="mt-5 py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Task Management System</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="docs.php" class="text-decoration-none">Documentation</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript dependencies - load at end of page -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="/assets/js/validation.js" defer></script>
    
    <!-- Lazy loading script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Lazy load images
            let lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));
            
            if ("IntersectionObserver" in window) {
                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            let lazyImage = entry.target;
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove("lazy");
                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });
                
                lazyImages.forEach(function(lazyImage) {
                    lazyImageObserver.observe(lazyImage);
                });
            } else {
                // Fallback for browsers that don't support IntersectionObserver
                let active = false;
                
                const lazyLoad = function() {
                    if (active === false) {
                        active = true;
                        
                        setTimeout(function() {
                            lazyImages.forEach(function(lazyImage) {
                                if ((lazyImage.getBoundingClientRect().top <= window.innerHeight && lazyImage.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyImage).display !== "none") {
                                    lazyImage.src = lazyImage.dataset.src;
                                    lazyImage.classList.remove("lazy");
                                    
                                    lazyImages = lazyImages.filter(function(image) {
                                        return image !== lazyImage;
                                    });
                                    
                                    if (lazyImages.length === 0) {
                                        document.removeEventListener("scroll", lazyLoad);
                                        window.removeEventListener("resize", lazyLoad);
                                        window.removeEventListener("orientationchange", lazyLoad);
                                    }
                                }
                            });
                            
                            active = false;
                        }, 200);
                    }
                };
                
                document.addEventListener("scroll", lazyLoad);
                window.addEventListener("resize", lazyLoad);
                window.addEventListener("orientationchange", lazyLoad);
                lazyLoad();
            }
        });
    </script>
    
    <?php 
    // End output buffering and send minified HTML to browser
    $html = ob_get_clean();
    echo minify_html($html);
    ?>
</body>
</html>
