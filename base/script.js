// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Close mobile menu if open
                navMenu.classList.remove('active');
            }
        });
    });

    // Initialize calculator result visibility
    const calculatorResult = document.getElementById('calculatorResult');
    if (calculatorResult) {
        calculatorResult.style.display = 'none';
    }
});

// Tax Calculation Function
function calculateTax() {
    const income = parseFloat(document.getElementById('annualIncome').value);
    const taxType = document.getElementById('taxType').value;
    const resultElement = document.getElementById('calculatorResult');
    
    if (!income || income <= 0) {
        alert('Please enter a valid income amount');
        return;
    }

    let tax = 0;
    let taxableIncome = income;
    
    // Simplified tax calculation 
    if (taxType === 'paye') {
        // Personal relief and other deductions
        const personalRelief = 4800; // Annual personal relief
        taxableIncome = Math.max(0, income - personalRelief);
        
        // Tax brackets rates
        if (taxableIncome > 84000) {
            tax = (taxableIncome - 84000) * 0.375 + 20700;
        } else if (taxableIncome > 54000) {
            tax = (taxableIncome - 54000) * 0.3 + 8700;
        } else if (taxableIncome > 42000) {
            tax = (taxableIncome - 42000) * 0.225 + 4500;
        } else if (taxableIncome > 36000) {
            tax = (taxableIncome - 36000) * 0.2 + 2700;
        } else if (taxableIncome > 30000) {
            tax = (taxableIncome - 30000) * 0.175 + 1500;
        } else if (taxableIncome > 24000) {
            tax = (taxableIncome - 24000) * 0.15 + 600;
        } else if (taxableIncome > 18000) {
            tax = (taxableIncome - 18000) * 0.125;
        }
    } else if (taxType === 'company') {
        // Company tax rate 
        tax = taxableIncome * 0.30; // 30% company tax
    } else if (taxType === 'vat') {
        // VAT calculation 
        const vatRate = 0.16; // 16% VAT
        tax = taxableIncome * vatRate;
    }

    const monthlyTax = tax / 12;
    
    // Update results display
    document.getElementById('resultIncome').textContent = `ZMW ${income.toLocaleString()}`;
    document.getElementById('resultTaxable').textContent = `ZMW ${taxableIncome.toLocaleString()}`;
    document.getElementById('resultAnnualTax').textContent = `ZMW ${tax.toLocaleString()}`;
    document.getElementById('resultMonthlyTax').textContent = `ZMW ${monthlyTax.toLocaleString()}`;
    
    // Show results
    resultElement.style.display = 'block';
    
    // Add animation
    resultElement.style.animation = 'fadeIn 0.5s ease-in';
}

// Form Validation Example
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'red';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });

    return isValid;
}

// Add fadeIn animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);

// Service Card Interactions
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// News Card Interactions
document.querySelectorAll('.news-card').forEach(card => {
    card.addEventListener('click', function() {
        // Add click functionality for news cards
        const link = this.querySelector('.news-link');
        if (link) {
            window.location.href = link.href;
        }
    });
});