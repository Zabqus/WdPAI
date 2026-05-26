const toggle = document.getElementById('toggle-password');
const pwd    = document.getElementById('password');
if (toggle && pwd) {
    toggle.addEventListener('click', () => {
        const isText = pwd.type === 'text';
        pwd.type = isText ? 'password' : 'text';
        toggle.querySelector('i').className = isText ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
    });
}
