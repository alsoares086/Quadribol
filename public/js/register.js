document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorMessage = document.getElementById('errorMessage');
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (password !== confirmPassword) {
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'As senhas não coincidem';
        return;
    }
    
    const formData = {
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        password: password
    };

    console.log('Sending registration data:', formData); // Debug log
    
    try {
        const response = await fetch('/Quadribol/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        console.log('Server response:', data); // Debug log
        
        if (data.status === 'success') {
            alert('Registro realizado com sucesso! Faça login para continuar.');
            window.location.href = 'login.html';
        } else {
            errorMessage.style.display = 'block';
            errorMessage.textContent = data.message || 'Erro ao registrar. Tente novamente.';
        }
    } catch (error) {
        console.error('Error:', error);
        errorMessage.style.display = 'block';
        errorMessage.textContent = 'Erro ao registrar. Tente novamente.';
    }
});