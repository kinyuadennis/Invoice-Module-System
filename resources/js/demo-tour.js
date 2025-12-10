/**
 * Demo Tour Configuration
 * Interactive walkthrough using Shepherd.js
 */

import { Tour } from 'shepherd.js';
import 'shepherd.js/dist/css/shepherd.css';
import * as animations from './demo-animations.js';

// Demo data
const demoData = {
    signup: {
        name: 'John Mwangi',
        email: 'john@example.com',
        password: 'SecurePass123!',
    },
    company: {
        name: "John's Garage",
        email: 'info@johnsgarage.co.ke',
        phone: '+254700123456',
        address: '123 Main Street, Nairobi, Kenya',
        kraPin: 'P012345678A',
    },
    client: {
        name: 'ABC Corporation',
        email: 'billing@abccorp.com',
        phone: '+254711234567',
    },
    invoice: {
        items: [
            { description: 'Consultation Services', quantity: 10, unitPrice: 5000 },
            { description: 'Technical Support', quantity: 5, unitPrice: 3000 },
        ],
    },
};

let tour = null;
let currentStep = 0;

/**
 * Initialize the demo tour
 */
export function initDemoTour() {
    if (tour) {
        tour.destroy();
    }

    const container = document.getElementById('demo-walkthrough-container');
    if (!container) {
        console.error('Demo container not found');
        return null;
    }

    // Clear loading state
    container.innerHTML = '';

    tour = new Tour({
        useModalOverlay: false, // We're using our own modal
        defaultStepOptions: {
            cancelIcon: {
                enabled: false, // We handle closing in the modal
            },
            scrollTo: false, // No scrolling needed in modal
            classes: 'demo-tour-step',
            canClickTarget: false,
            when: {
                show: function() {
                    updateDemoContent(this.id);
                    announceStep(this.title);
                },
            },
        },
    });

    // Add steps
    addWelcomeStep();
    addSignupStep();
    addCompanyStep();
    addInvoiceStep();
    addPreviewStep();
    addPaymentStep();

    return tour;
}

/**
 * Update demo content based on step
 */
function updateDemoContent(stepId) {
    const container = document.getElementById('demo-walkthrough-container');
    if (!container) return;

    let content = '';
    
    switch(stepId) {
        case 'welcome-step':
            content = getWelcomeContent();
            break;
        case 'signup-step':
            content = getSignupContent();
            setTimeout(() => animateSignupStep(), 300);
            break;
        case 'company-step':
            content = getCompanyContent();
            setTimeout(() => animateCompanyStep(), 300);
            break;
        case 'invoice-step':
            content = getInvoiceContent();
            setTimeout(() => animateInvoiceStep(), 300);
            break;
        case 'preview-step':
            content = getPreviewContent();
            break;
        case 'payment-step':
            content = getPaymentContent();
            break;
    }
    
    container.innerHTML = content;
}

/**
 * Get welcome step content
 */
function getWelcomeContent() {
    return `
        <div class="text-center py-8">
            <div class="mb-6">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Welcome to InvoiceHub</h3>
                <p class="text-gray-600 max-w-md mx-auto">
                    Let's take a quick interactive tour to see how easy it is to create professional invoices.
                </p>
            </div>
            <div class="grid grid-cols-5 gap-2 max-w-md mx-auto mt-8">
                ${[1, 2, 3, 4, 5].map(i => `
                    <div class="h-2 rounded ${i === 1 ? 'bg-blue-600' : 'bg-gray-200'}"></div>
                `).join('')}
            </div>
            <p class="text-sm text-gray-500 mt-4">Step 1 of 6</p>
        </div>
    `;
}

/**
 * Get signup step content
 */
function getSignupContent() {
    return `
        <div class="max-w-md mx-auto">
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    <h3 class="text-lg font-semibold text-gray-900">Sign Up</h3>
                </div>
                <p class="text-sm text-gray-600 ml-10">Create your account in seconds</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm" id="signup-form-demo">
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="demo-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="demo-email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your email" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="demo-password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Create a password" />
                    </div>
                    <button type="button" class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 font-medium transition-colors" id="demo-signup-btn">
                        Create Account
                    </button>
                </form>
            </div>
            <div class="grid grid-cols-5 gap-2 max-w-md mx-auto mt-6">
                ${[1, 2, 3, 4, 5].map(i => `
                    <div class="h-2 rounded ${i <= 2 ? 'bg-blue-600' : 'bg-gray-200'}"></div>
                `).join('')}
            </div>
            <p class="text-sm text-gray-500 mt-2 text-center">Step 2 of 6</p>
        </div>
    `;
}

/**
 * Get company step content
 */
function getCompanyContent() {
    return `
        <div class="max-w-md mx-auto">
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    <h3 class="text-lg font-semibold text-gray-900">Add Your Company</h3>
                </div>
                <p class="text-sm text-gray-600 ml-10">Set up your business profile</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm" id="company-form-demo">
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <input type="text" id="demo-company-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="demo-company-email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" id="demo-company-phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">KRA PIN</label>
                        <input type="text" id="demo-company-kra" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-sm text-gray-500">Click to upload logo</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-5 gap-2 max-w-md mx-auto mt-6">
                ${[1, 2, 3, 4, 5].map(i => `
                    <div class="h-2 rounded ${i <= 3 ? 'bg-blue-600' : 'bg-gray-200'}"></div>
                `).join('')}
            </div>
            <p class="text-sm text-gray-500 mt-2 text-center">Step 3 of 6</p>
        </div>
    `;
}

/**
 * Get invoice step content
 */
function getInvoiceContent() {
    return `
        <div class="max-w-2xl mx-auto">
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    <h3 class="text-lg font-semibold text-gray-900">Create Invoice</h3>
                </div>
                <p class="text-sm text-gray-600 ml-10">Add items and watch totals calculate automatically</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm" id="invoice-form-demo">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                        <select id="demo-client-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select client...</option>
                            <option value="1">${demoData.client.name}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Items</label>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-gray-700 font-medium">Description</th>
                                        <th class="px-4 py-2 text-right text-gray-700 font-medium">Qty</th>
                                        <th class="px-4 py-2 text-right text-gray-700 font-medium">Price</th>
                                        <th class="px-4 py-2 text-right text-gray-700 font-medium">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="demo-items-table" class="bg-white">
                                    <tr class="border-t border-gray-200">
                                        <td class="px-4 py-3" id="demo-item-1-desc"></td>
                                        <td class="px-4 py-3 text-right" id="demo-item-1-qty"></td>
                                        <td class="px-4 py-3 text-right" id="demo-item-1-price"></td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900" id="demo-item-1-total"></td>
                                    </tr>
                                    <tr class="border-t border-gray-200">
                                        <td class="px-4 py-3" id="demo-item-2-desc"></td>
                                        <td class="px-4 py-3 text-right" id="demo-item-2-qty"></td>
                                        <td class="px-4 py-3 text-right" id="demo-item-2-price"></td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900" id="demo-item-2-total"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="border-t-2 border-gray-200 pt-4 space-y-2 bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-900" id="demo-subtotal">KES 0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">VAT (16%):</span>
                            <span class="font-medium text-gray-900" id="demo-vat">KES 0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Platform Fee (3%):</span>
                            <span class="font-medium text-gray-900" id="demo-fee">KES 0</span>
                        </div>
                        <div class="flex justify-between font-bold text-base border-t-2 border-gray-300 pt-2 mt-2">
                            <span class="text-gray-900">Total:</span>
                            <span class="text-blue-600" id="demo-total">KES 0</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-5 gap-2 max-w-md mx-auto mt-6">
                ${[1, 2, 3, 4, 5].map(i => `
                    <div class="h-2 rounded ${i <= 4 ? 'bg-blue-600' : 'bg-gray-200'}"></div>
                `).join('')}
            </div>
            <p class="text-sm text-gray-500 mt-2 text-center">Step 4 of 6</p>
        </div>
    `;
}

/**
 * Get preview step content
 */
function getPreviewContent() {
    return `
        <div class="max-w-2xl mx-auto">
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
                    <h3 class="text-lg font-semibold text-gray-900">Preview & Download</h3>
                </div>
                <p class="text-sm text-gray-600 ml-10">See your professional invoice before sending</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm" id="preview-demo">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 bg-gray-50 min-h-[300px] flex flex-col items-center justify-center mb-4">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Professional Invoice</h4>
                        <p class="text-sm text-gray-600">INV-2025-001</p>
                        <p class="text-sm text-gray-600 mt-2">${demoData.client.name}</p>
                        <p class="text-sm font-semibold text-blue-600 mt-4">KES 65,000</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button class="flex-1 bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center justify-center gap-2" id="demo-download-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </button>
                    <button class="flex-1 bg-green-600 text-white py-2.5 rounded-lg hover:bg-green-700 font-medium transition-colors flex items-center justify-center gap-2" id="demo-send-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Send to Client
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-5 gap-2 max-w-md mx-auto mt-6">
                ${[1, 2, 3, 4, 5].map(i => `
                    <div class="h-2 rounded ${i <= 5 ? 'bg-blue-600' : 'bg-gray-200'}"></div>
                `).join('')}
            </div>
            <p class="text-sm text-gray-500 mt-2 text-center">Step 5 of 6</p>
        </div>
    `;
}

/**
 * Get payment step content
 */
function getPaymentContent() {
    return `
        <div class="max-w-md mx-auto">
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold">✓</span>
                    <h3 class="text-lg font-semibold text-gray-900">Get Paid</h3>
                </div>
                <p class="text-sm text-gray-600 ml-10">Track payments and get notified</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-blue-50 border-2 border-green-200 rounded-lg p-8 text-center" id="payment-demo">
                <div class="mb-6">
                    <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-green-800 mb-2">Payment Received!</h3>
                    <p class="text-lg font-semibold text-green-700 mb-1">KES 65,000</p>
                    <p class="text-sm text-gray-600">via M-Pesa</p>
                </div>
                <div class="bg-white rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600 mb-2">Invoice automatically marked as paid</p>
                    <div class="flex items-center justify-center gap-2 text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">Status: Paid</span>
                    </div>
                </div>
                <div class="bg-blue-600 text-white rounded-lg p-4">
                    <p class="text-sm font-medium mb-2">Ready to get started?</p>
                    <p class="text-xs opacity-90">Create your first invoice in under 2 minutes</p>
                </div>
            </div>
            <div class="grid grid-cols-5 gap-2 max-w-md mx-auto mt-6">
                ${[1, 2, 3, 4, 5].map(() => `
                    <div class="h-2 rounded bg-blue-600"></div>
                `).join('')}
            </div>
            <p class="text-sm text-gray-500 mt-2 text-center">Step 6 of 6 - Complete!</p>
        </div>
    `;
}

/**
 * Add welcome step
 */
function addWelcomeStep() {
    tour.addStep({
        id: 'welcome-step',
        title: 'Welcome to InvoiceHub',
        text: 'Let\'s see how easy it is to create professional invoices.',
        buttons: [
            {
                text: 'Skip Demo',
                action: () => {
                    if (window.stopDemoTour) window.stopDemoTour();
                },
                classes: 'shepherd-button-secondary',
            },
            {
                text: 'Start Demo',
                action: tour.next,
                classes: 'shepherd-button-primary',
            },
        ],
    });
}

/**
 * Add signup step
 */
function addSignupStep() {
    tour.addStep({
        id: 'signup-step',
        title: 'Step 1: Sign Up',
        text: 'Create your account in seconds. Watch the form auto-fill!',
        buttons: [
            {
                text: 'Previous',
                action: tour.back,
                classes: 'shepherd-button-secondary',
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary',
            },
        ],
    });
}

/**
 * Add company step
 */
function addCompanyStep() {
    tour.addStep({
        id: 'company-step',
        title: 'Step 2: Add Your Company',
        text: 'Set up your business profile with branding and details.',
        buttons: [
            {
                text: 'Previous',
                action: tour.back,
                classes: 'shepherd-button-secondary',
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary',
            },
        ],
    });
}

/**
 * Add invoice step
 */
function addInvoiceStep() {
    tour.addStep({
        id: 'invoice-step',
        title: 'Step 3: Create Invoice',
        text: 'Add items and watch totals calculate automatically in real-time.',
        buttons: [
            {
                text: 'Previous',
                action: tour.back,
                classes: 'shepherd-button-secondary',
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary',
            },
        ],
    });
}

/**
 * Add preview step
 */
function addPreviewStep() {
    tour.addStep({
        id: 'preview-step',
        title: 'Step 4: Preview & Download',
        text: 'Preview your professional invoice and download as PDF or send directly.',
        buttons: [
            {
                text: 'Previous',
                action: tour.back,
                classes: 'shepherd-button-secondary',
            },
            {
                text: 'Next',
                action: tour.next,
                classes: 'shepherd-button-primary',
            },
        ],
    });
}

/**
 * Add payment step
 */
function addPaymentStep() {
    tour.addStep({
        id: 'payment-step',
        title: 'Step 5: Get Paid',
        text: 'Track payments and get notified when clients pay via M-Pesa or bank transfer.',
        buttons: [
            {
                text: 'Previous',
                action: tour.back,
                classes: 'shepherd-button-secondary',
            },
            {
                text: 'Try It Now',
                action: () => {
                    tour.complete();
                    window.location.href = '/register';
                },
                classes: 'shepherd-button-primary',
            },
        ],
    });
}

/**
 * Animate signup step
 */
async function animateSignupStep() {
    await animations.wait(500);
    await animations.fillField('#demo-name', demoData.signup.name);
    await animations.wait(300);
    await animations.fillField('#demo-email', demoData.signup.email);
    await animations.wait(300);
    await animations.fillField('#demo-password', demoData.signup.password);
    await animations.wait(500);
    await animations.highlightElement('#demo-signup-btn', 1000);
}

/**
 * Animate company step
 */
async function animateCompanyStep() {
    await animations.wait(500);
    await animations.fillField('#demo-company-name', demoData.company.name);
    await animations.wait(300);
    await animations.fillField('#demo-company-email', demoData.company.email);
    await animations.wait(300);
    await animations.fillField('#demo-company-phone', demoData.company.phone);
    await animations.wait(300);
    await animations.fillField('#demo-company-kra', demoData.company.kraPin);
}

/**
 * Animate invoice step
 */
async function animateInvoiceStep() {
    await animations.wait(500);
    
    // Select client
    const clientSelect = document.querySelector('#demo-client-select');
    if (clientSelect) {
        clientSelect.value = '1';
        clientSelect.dispatchEvent(new Event('change', { bubbles: true }));
        await animations.highlightElement(clientSelect, 800);
    }
    
    await animations.wait(500);
    
    // Fill items
    const items = demoData.invoice.items;
    let subtotal = 0;
    
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        const total = item.quantity * item.unitPrice;
        subtotal += total;
        
        const descEl = document.querySelector(`#demo-item-${i + 1}-desc`);
        const qtyEl = document.querySelector(`#demo-item-${i + 1}-qty`);
        const priceEl = document.querySelector(`#demo-item-${i + 1}-price`);
        const totalEl = document.querySelector(`#demo-item-${i + 1}-total`);
        
        if (descEl) descEl.textContent = item.description;
        if (qtyEl) qtyEl.textContent = item.quantity;
        if (priceEl) priceEl.textContent = `KES ${item.unitPrice.toLocaleString('en-KE')}`;
        if (totalEl) {
            await animations.animateNumber(totalEl, total, 500, 'KES ');
        }
        
        await animations.wait(400);
    }
    
    // Calculate totals
    const vat = subtotal * 0.16;
    const fee = (subtotal + vat) * 0.03;
    const grandTotal = subtotal + vat + fee;
    
    await animations.animateNumber(document.querySelector('#demo-subtotal'), subtotal, 500, 'KES ');
    await animations.wait(200);
    await animations.animateNumber(document.querySelector('#demo-vat'), vat, 500, 'KES ');
    await animations.wait(200);
    await animations.animateNumber(document.querySelector('#demo-fee'), fee, 500, 'KES ');
    await animations.wait(200);
    await animations.animateNumber(document.querySelector('#demo-total'), grandTotal, 500, 'KES ');
}

/**
 * Announce step to screen readers
 */
function announceStep(title) {
    const announcement = document.getElementById('demo-announcements');
    if (announcement) {
        announcement.textContent = `Demo step: ${title}`;
        setTimeout(() => {
            announcement.textContent = '';
        }, 1000);
    }
}

/**
 * Start the demo tour
 */
export function startDemoTour() {
    if (!tour) {
        initDemoTour();
    }
    if (tour) {
        tour.start();
    }
}

/**
 * Stop the demo tour
 */
export function stopDemoTour() {
    if (tour) {
        tour.cancel();
    }
}
