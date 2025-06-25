const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
	function updateCartCount() {
        document.getElementById('cart-count').textContent = cart.reduce((acc, item) => acc + item.quantity, 0);
    }

    function saveCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
    }

    function addToCart(product) {
        const existing = cart.find(p => p.id === product.id);
        if (existing) {
            existing.quantity += 1;
        } else {
            product.quantity = 1;
            cart.push(product);
        }
        saveCart();
    }

    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', () => {
            const product = {
                id: button.dataset.id,
                name: button.dataset.name,
                price: parseFloat(button.dataset.price)
            };

            addToCart(product);

        });
    });
    updateCartCount();

    document.getElementById('button-payment').addEventListener('click', async () => {

        if (cart.length == 0) {
            alert("votre panier est vide")
            return
        } 

        let panier = [];
        cart.forEach(product => {
            panier.push({
                id: product.id,
                quantity: product.quantity
            });
        });
        
        let response = await fetch('/stripe/create/link', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                products: panier
            }),
        });

        const data = await response.json();

        if (data.url) {
            window.location.href = data.url;
            localStorage.clear();
        } else {
            alert("Erreur : lien non reçu.");
        }

    })