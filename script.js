// script.js

document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const carsGrid = document.getElementById('carsGrid');
    const emptyState = document.getElementById('emptyState');
    
    // Get modal elements
    const carModal = document.getElementById('carModal');
    const modalClose = document.querySelector('.modal-close');
    const modalRentBtn = document.getElementById('modalRentBtn');

    // Load all cars on page load
    loadCars('all');

    // Filter buttons event listeners
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            const category = this.getAttribute('data-filter');
            loadCars(category);
        });
    });

    // Close modal when clicking the close button
    if (modalClose) {
        modalClose.addEventListener('click', function() {
            if (carModal) {
                carModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }

    // Close modal when clicking outside the modal content
    if (carModal) {
        carModal.addEventListener('click', function(e) {
            if (e.target === carModal) {
                carModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }

    // Handle modal Rent Now button click
    if (modalRentBtn) {
        modalRentBtn.addEventListener('click', function() {
            const carId = this.getAttribute('data-car-id');
            const carName = document.getElementById('modalCarName')?.textContent || 'this car';
            const carPrice = document.getElementById('modalCarPrice')?.innerText || '0';
            
            if (carId) {
                // Store car info and redirect to login/register
                redirectToLoginRegister(carId, carName, carPrice);
            } else {
                alert('Please select a car first');
            }
        });
    }

    function loadCars(category) {
        // Load worker-added cars from database
        console.log('Loading worker cars for category:', category);
        
        fetch('processor/get_worker_cars.php?category=' + category)
            .then(response => response.json())
            .then(data => {
                let cars = [];
                
                // Check if we got worker cars from database
                if (Array.isArray(data) && data.length > 0) {
                    cars = data;
                    console.log('Loaded worker cars:', cars.length, 'cars from database');
                } else if (data.cars && Array.isArray(data.cars) && data.cars.length > 0) {
                    cars = data.cars;
                    console.log('Loaded worker cars (cars array):', cars.length, 'cars from database');
                } else {
                    // Fallback to embedded data if no worker cars
                    cars = getFallbackCars(category);
                    console.log('No worker cars found, using fallback:', cars.length, 'cars');
                }
                
                displayCars(cars);
            })
            .catch(error => {
                console.log('Worker API failed, using fallback cars:', error);
                const cars = getFallbackCars(category);
                displayCars(cars);
            });
    }
    
    function getFallbackCars(category) {
        const allCars = [
            {
                id: 1,
                car_name: "Toyota Camry",
                brand: "Toyota",
                model: "Camry",
                year: "2023",
                price_per_day: 2500,
                transmission: "Automatic",
                fuel_type: "Gasoline",
                seating_capacity: 5,
                category: "economy",
                description: "Comfortable and reliable sedan",
                image: "assets/images/1777003333_69eaeb457d014.jpg"
            },
            {
                id: 2,
                car_name: "Honda Civic",
                brand: "Honda",
                model: "Civic",
                year: "2023",
                price_per_day: 2200,
                transmission: "Manual",
                fuel_type: "Gasoline",
                seating_capacity: 5,
                category: "economy",
                description: "Efficient and sporty compact car",
                image: "assets/images/1777003840_69eaed40b3ca5.jpg"
            },
            {
                id: 3,
                car_name: "Ford Mustang",
                brand: "Ford",
                model: "Mustang",
                year: "2023",
                price_per_day: 4500,
                transmission: "Manual",
                fuel_type: "Gasoline",
                seating_capacity: 4,
                category: "sports",
                description: "Powerful sports car experience",
                image: "assets/images/car-png-39073.png"
            },
            {
                id: 4,
                car_name: "Toyota RAV4",
                brand: "Toyota",
                model: "RAV4",
                year: "2023",
                price_per_day: 3500,
                transmission: "Automatic",
                fuel_type: "Gasoline",
                seating_capacity: 7,
                category: "suv",
                description: "Spacious SUV for families",
                image: "assets/images/cdb01d20b2b15e4152cfa2b82cd6fb01.jpg"
            },
            {
                id: 5,
                car_name: "BMW 3 Series",
                brand: "BMW",
                model: "3 Series",
                year: "2023",
                price_per_day: 5500,
                transmission: "Automatic",
                fuel_type: "Gasoline",
                seating_capacity: 5,
                category: "luxury",
                description: "Premium luxury sedan experience",
                image: "assets/images/download (4).jpg"
            },
            {
                id: 6,
                car_name: "Mitsubishi Montero",
                brand: "Mitsubishi",
                model: "Montero",
                year: "2023",
                price_per_day: 3800,
                transmission: "Automatic",
                fuel_type: "Diesel",
                seating_capacity: 7,
                category: "suv",
                description: "Rugged SUV for adventure",
                image: "assets/images/d0f4687fcffd30cdd82321dd957cec53.jpg"
            },
            {
                id: 7,
                car_name: "Mercedes-Benz C-Class",
                brand: "Mercedes-Benz",
                model: "C-Class",
                year: "2023",
                price_per_day: 6000,
                transmission: "Automatic",
                fuel_type: "Gasoline",
                seating_capacity: 5,
                category: "luxury",
                description: "Ultimate luxury and comfort",
                image: "assets/images/pngegg (1).png"
            },
            {
                id: 8,
                car_name: "Hyundai Tucson",
                brand: "Hyundai",
                model: "Tucson",
                year: "2023",
                price_per_day: 2800,
                transmission: "Automatic",
                fuel_type: "Gasoline",
                seating_capacity: 5,
                category: "suv",
                description: "Modern compact SUV",
                image: "assets/images/f828a23bfed13d4f79c0b34d9b6cf8a1.jpg"
            }
        ];
        
        // Filter by category if specified
        if (category && category !== 'all') {
            return allCars.filter(car => car.category === category);
        }
        
        return allCars;
    }

    function displayCars(cars) {
        if (!carsGrid) return;
        
        if (cars.length === 0) {
            carsGrid.innerHTML = '';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }

        if (emptyState) emptyState.style.display = 'none';

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
        
        // Attach event listeners to all rent buttons after they're created
        attachRentButtonListeners();
    }

    function createCarCard(car) {
        const imagePath = car.image && car.image !== '' ? car.image : 'assets/images/default-car.svg';
        const formattedPrice = car.price_per_day ? `₱${parseFloat(car.price_per_day).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '₱0';
        
        // Create additional info badges
        const transmission = car.transmission || 'N/A';
        const fuelType = car.fuel_type || 'N/A';
        const seats = car.seating_capacity || 'N/A';
        const year = car.year || '';
        
        return `
            <div class="car-card" data-car-id="${car.id}">
                <img src="${imagePath}" alt="${escapeHtml(car.car_name)}" class="car-image" onerror="this.src='assets/images/default-car.svg'">
                <div class="car-info">
                    <h3 class="car-name">${escapeHtml(car.car_name)}</h3>
                    <p class="car-brand">${escapeHtml(car.brand || '')} ${escapeHtml(car.model || '')} ${year ? '(' + year + ')' : ''}</p>
                    
                    <div class="car-specs-badges">
                        <span class="spec-badge transmission">${escapeHtml(transmission)}</span>
                        <span class="spec-badge fuel">${escapeHtml(fuelType)}</span>
                        <span class="spec-badge seats">${escapeHtml(seats)} Seats</span>
                    </div>
                    
                    <p class="car-price">${formattedPrice} <span>/ day</span></p>
                    <button class="rent-btn" data-car-id="${car.id}" data-car-name="${escapeHtml(car.car_name)}" data-car-price="${car.price_per_day}" data-car-image="${imagePath}" data-car-brand="${escapeHtml(car.brand || '')}" data-car-model="${escapeHtml(car.model || '')}" data-car-transmission="${escapeHtml(car.transmission || 'N/A')}" data-car-fuel="${escapeHtml(car.fuel_type || 'N/A')}" data-car-seats="${car.seating_capacity || 'N/A'}" data-car-year="${escapeHtml(car.year || '')}" data-car-description="${escapeHtml(car.description || '')}" data-car-category="${escapeHtml(car.category || '')}">Rent Now</button>
                </div>
            </div>
        `;
    }

    function attachRentButtonListeners() {
        const rentBtns = document.querySelectorAll('.rent-btn');
        rentBtns.forEach(btn => {
            btn.removeEventListener('click', handleRentClick);
            btn.addEventListener('click', handleRentClick);
        });
    }

    function handleRentClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = e.currentTarget;
        const carId = btn.getAttribute('data-car-id');
        const carName = btn.getAttribute('data-car-name');
        const carPrice = btn.getAttribute('data-car-price');
        const carImage = btn.getAttribute('data-car-image');
        const carBrand = btn.getAttribute('data-car-brand');
        const carModel = btn.getAttribute('data-car-model');
        const carTransmission = btn.getAttribute('data-car-transmission');
        const carFuel = btn.getAttribute('data-car-fuel');
        const carSeats = btn.getAttribute('data-car-seats');
        
        // Open modal with car details
        openModal(carId, carName, carPrice, carImage, carBrand, carModel, carTransmission, carFuel, carSeats);
    }

    // Function to open modal with car details
    window.openModal = function(carId, carName, carPrice, carImage, carBrand, carModel, carTransmission, carFuel, carSeats) {
        const carModal = document.getElementById('carModal');
        const modalCarName = document.getElementById('modalCarName');
        const modalCarPrice = document.getElementById('modalCarPrice');
        const modalCarImage = document.getElementById('modalCarImage');
        const modalRentBtn = document.getElementById('modalRentBtn');
        const modalCarFeatures = document.getElementById('modalCarFeatures');
        const modalCarSpecs = document.getElementById('modalCarSpecs');
        
        if (!carModal) return;
        
        // Set modal content
        if (modalCarName) modalCarName.textContent = carName || 'Car Name';
        if (modalCarPrice) modalCarPrice.innerHTML = `₱${parseFloat(carPrice).toLocaleString()} <span>/ day</span>`;
        if (modalCarImage) modalCarImage.src = carImage || 'assets/images/default-car.jpg';
        if (modalRentBtn) modalRentBtn.setAttribute('data-car-id', carId);
        
        // Set features
        if (modalCarFeatures) {
            modalCarFeatures.innerHTML = `
                <div class="feature-item"><i class="fas fa-tag"></i> ${escapeHtml(carBrand)} ${escapeHtml(carModel)}</div>
            `;
        }
        
        // Set specifications
        if (modalCarSpecs) {
            modalCarSpecs.innerHTML = `
                <li><i class="fas fa-cogs"></i> Transmission: ${escapeHtml(carTransmission)}</li>
                <li><i class="fas fa-gas-pump"></i> Fuel Type: ${escapeHtml(carFuel)}</li>
                <li><i class="fas fa-users"></i> Seating Capacity: ${escapeHtml(carSeats)} seats</li>
            `;
        }
        
        // Show modal
        carModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };
});

// ============ REDIRECT TO LOGIN/REGISTER FUNCTION ============
window.redirectToLoginRegister = function(carId, carName, carPrice) {
    // Store car information in localStorage for after login
    const carData = {
        id: carId,
        name: carName,
        price: carPrice,
    };
    
    localStorage.setItem('selectedCar', JSON.stringify(carData));
    
    // Also store in sessionStorage as backup
    sessionStorage.setItem('selectedCar', JSON.stringify(carData));
    
    // Redirect to login/register page
    window.location.href = 'p_login/login.php';
};

// Helper function to escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ============ CHECK IF USER RETURNED FROM LOGIN ============
document.addEventListener('DOMContentLoaded', function() {
    // Check if user just logged in and has a selected car
    const selectedCar = localStorage.getItem('selectedCar') || sessionStorage.getItem('selectedCar');
    
    if (selectedCar) {
        try {
            const carData = JSON.parse(selectedCar);
            
            // Check if data is not too old (within 5 minutes)
            const isRecent = (new Date().getTime() - (carData.timestamp || 0)) < 300000;
            
            if (carData.id && isRecent) {
                // Show notification that they can book the car
                showNotification(`Welcome back! You can now book the ${carData.name}.`, 'success');
                
                // Clear the stored car after showing notification
                setTimeout(() => {
                    localStorage.removeItem('selectedCar');
                    sessionStorage.removeItem('selectedCar');
                }, 3000);
            } else if (carData.id) {
                // Clear old data
                localStorage.removeItem('selectedCar');
                sessionStorage.removeItem('selectedCar');
            }
        } catch(e) {
            console.error('Error parsing selected car:', e);
        }
    }
});

// Notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')}"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : (type === 'error' ? '#dc3545' : '#17a2b8')};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 10000;
        font-family: 'Poppins', sans-serif;
        animation: slideIn 0.3s ease;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Close modal with Escape key
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const carModal = document.getElementById('carModal');
            if (carModal && carModal.style.display === 'flex') {
                carModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    });
});