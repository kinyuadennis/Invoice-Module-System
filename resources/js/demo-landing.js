/**
 * Demo Tour Initialization for Landing Page
 * Simple demo manager without Shepherd.js complexity
 */

import { initDemo, nextStep, previousStep, getCurrentStep, isFirstStep, isLastStep } from './demo-manager.js';

// Make functions available globally
window.initDemo = initDemo;
window.nextDemoStep = nextStep;
window.previousDemoStep = previousStep;
window.getCurrentStep = getCurrentStep;
window.isFirstStep = isFirstStep;
window.isLastStep = isLastStep;

// Export for module imports
export { initDemo, nextStep, previousStep, getCurrentStep, isFirstStep, isLastStep };
