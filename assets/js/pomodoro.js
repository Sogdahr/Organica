let tiempoRestante = 25 * 60;
let intervale = null;
let enMarcha = false;

const pantallaTiempo = document.getElementById("pantalla-tiempo");
const btnIniciar = document.getElementById("btn-iniciar");
const btnPausar = document.getElementById("btn-pausar");
const btnReiniciar = document.getElementById("btn-reiniciar");
const inputDuracion = document.getElementById("duracion_pomodoro");

function actualizarPantalla() {
    const minutos = Math.floor(tiempoRestante / 60);
    const segundos = tiempoRestante % 60;

    pantallaTiempo.textContent = 
            String(minutos).padStart(2, "0") + ":" +
            String(segundos).padStart(2, "0");
}

function iniciarPomodoro() {
    if (enMarcha) {
        return;
    }

    enMarcha = true;

    intervalo = setInterval(function () {
        if (tiempoRestante > 0) {
            tiempoRestante--;
            actualizarPantalla();
        } else {
            clearInterval(intervalo);
            enMarcha = false;
            alert("Pomodoro terminado. Puedes guardar la sesión.");
        }
    }, 1000);
}

function pausarPomodoro () {
    clearInterval(intervalo);
    enMarcha = false;
}

function reiniciarPomodoro () {
    clearInterval(intervalo);
    tiempoRestante = 25 * 60;
    enMarcha = false;
    actualizarPantalla();
}

btnIniciar.addEventListener("click", iniciarPomodoro);
btnPausar.addEventListener("click", pausarPomodoro);
btnReiniciar.addEventListener("click", reiniciarPomodoro);

actualizarPantalla();