document.addEventListener('DOMContentLoaded', async e => {
    await fetch('../../backend/cuenta/logout.php', {method: 'POST', body: '{}'});
    await Swal.fire({
        title: "Cierre de Sesion!",
        text: `Cierre de sesion exitoso.`,
        icon: "success"
    });
    const a = document.createElement('a');
    a.href = '../general/index.php';
    a.click();
});