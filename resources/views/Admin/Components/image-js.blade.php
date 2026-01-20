<script>
    image.onchange = evt => {
        const [file] = image.files
        if (file) {
            document.getElementById("show_image").style.display = "block";
            show_image.src = URL.createObjectURL(file)
        }
    }
</script>
