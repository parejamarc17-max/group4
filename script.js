// script.js

document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const carsGrid = document.getElementById('carsGrid');
    const emptyState = document.getElementById('emptyState');

    // Load all cars on page load
    loadCars('all');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            // Load cars for the selected category
            const category = this.getAttribute('data-filter');
            loadCars(category);
        });
    });

    function loadCars(category) {
        fetch(`processor/get_cars.php?category=${category}`)
            .then(response => response.json())
            .then(cars => {
                displayCars(cars);
            })
            .catch(error => {
                console.error('Error loading cars:', error);
                carsGrid.innerHTML = '<p>Error loading cars. Please try again.</p>';
            });
    }

    function displayCars(cars) {
        if (cars.length === 0) {
            carsGrid.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';

        // Split cars into two sections of 4
        const section1 = cars.slice(0, 4);
        const section2 = cars.slice(4, 8);

        carsGrid.innerHTML = `
            <div class="cars-section" id="carsSection1">
                ${section1.map(car => createCarCard(car)).join('')}
            </div>
            <div class="cars-section" id="carsSection2">
                ${section2.map(car => createCarCard(car)).join('')}
            </div>
        `;
    }

    function createCarCard(car) {
        return `
            <div class="car-card" data-car-id="${car.id}">
                <img src="assets/images/download (4).jpg" alt="${car.car_name}" class="car-image">
                <div class="car-info">
                    <h3 class="car-name">${car.car_name}</h3>
                    <p class="car-price">$${car.price_per_day} / day</p>
                    <button class="rent-btn" onclick="openModal(${car.id}, '${car.car_name}', ${car.price_per_day})">Rent Now</button>
                </div>
            </div>
        `;
    }
});

// Function to open modal (placeholder, implement as needed)
function openModal(carId, carName, price) {
    // Implement modal opening logic
    console.log('Open modal for car:', carId, carName, price);
}