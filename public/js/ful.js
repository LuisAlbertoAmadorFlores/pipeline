const boton = document.getElementById("login");

// Escuchamos el clic DIRECTO
boton.addEventListener("click", () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch((err) => {
            alert(`Error: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
});
