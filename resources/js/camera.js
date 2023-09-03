function webcamApp() {
    return {
        video: null,
        canvas: null,
        image: null,

        init() {
            this.video = this.$refs.video;
            this.canvas = this.$refs.canvas;
            this.startWebcam();
        },

        startWebcam() {
            navigator.mediaDevices
                .getUserMedia({ video: true })
                .then((stream) => {
                    this.video.srcObject = stream;
                })
                .catch((error) => {
                    console.error("Error accessing webcam:", error);
                });
        },

        captureImage() {
            this.canvas.width = this.video.videoWidth;
            this.canvas.height = this.video.videoHeight;
            this.canvas.getContext("2d").drawImage(this.video, 0, 0);
            this.image = this.canvas.toDataURL("image/png");
            var fileInput = this.$refs.fileInput;
            fileInput.value = this.image;
            this.$refs.video.style.display = "none";
        },
    };
}
