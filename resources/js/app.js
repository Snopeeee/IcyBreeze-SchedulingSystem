import './bootstrap';
import '@phosphor-icons/web/regular';
import '@phosphor-icons/web/fill';

const money = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 });

document.querySelector('[data-menu-toggle]')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const menu = document.querySelector('[data-site-menu]');
    const open = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!open));
    menu?.classList.toggle('is-open', !open);
    button.querySelector('i')?.classList.toggle('ph-list', open);
    button.querySelector('i')?.classList.toggle('ph-x', !open);
    const accessibleLabel = button.querySelector('.sr-only');
    if (accessibleLabel) accessibleLabel.textContent = open ? 'Open menu' : 'Close menu';
});

document.querySelectorAll('[data-faq-button]').forEach((button) => {
    button.addEventListener('click', () => {
        const item = button.closest('[data-faq-item]');
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        item?.classList.toggle('is-open', !expanded);
    });
});

const reviewCarousel = document.querySelector('[data-review-carousel]');
if (reviewCarousel) {
    const track = reviewCarousel.querySelector('[data-review-track]');
    const slides = [...reviewCarousel.querySelectorAll('[data-review-slide]')];
    const dots = [...reviewCarousel.querySelectorAll('[data-review-dot]')];
    const previousButton = reviewCarousel.querySelector('[data-review-previous]');
    const nextButton = reviewCarousel.querySelector('[data-review-next]');
    const autoplayButton = reviewCarousel.querySelector('[data-review-autoplay]');
    const status = reviewCarousel.querySelector('[data-review-status]');
    const announcement = reviewCarousel.querySelector('[data-review-announcement]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const interval = Number(reviewCarousel.dataset.autoplayInterval || 6500);
    let activeIndex = 0;
    let timer;
    let interactionPaused = false;
    let userPaused = reducedMotion.matches;

    const stopRotation = () => {
        window.clearInterval(timer);
        timer = undefined;
    };

    const updateAutoplayButton = () => {
        if (!autoplayButton) return;
        const icon = autoplayButton.querySelector('i');
        autoplayButton.setAttribute('aria-pressed', String(userPaused));
        autoplayButton.setAttribute('aria-label', userPaused ? 'Start automatic rotation' : 'Pause automatic rotation');
        if (icon) icon.className = userPaused ? 'ph ph-play' : 'ph ph-pause';
    };

    const startRotation = () => {
        stopRotation();
        if (userPaused || interactionPaused || document.hidden || slides.length < 2) return;
        timer = window.setInterval(() => showSlide(activeIndex + 1), interval);
    };

    const showSlide = (requestedIndex, announce = false) => {
        activeIndex = (requestedIndex + slides.length) % slides.length;
        if (track) track.style.transform = `translate3d(-${activeIndex * 100}%, 0, 0)`;

        slides.forEach((slide, index) => {
            const isActive = index === activeIndex;
            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
        });

        dots.forEach((dot, index) => {
            const isActive = index === activeIndex;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-current', String(isActive));
        });

        if (status) status.textContent = `${activeIndex + 1} of ${slides.length}`;
        if (announce && announcement) {
            const reviewer = slides[activeIndex]?.dataset.reviewer || 'customer';
            announcement.textContent = `Showing recommendation ${activeIndex + 1} of ${slides.length} from ${reviewer}.`;
        }
        startRotation();
    };

    previousButton?.addEventListener('click', () => showSlide(activeIndex - 1, true));
    nextButton?.addEventListener('click', () => showSlide(activeIndex + 1, true));
    dots.forEach((dot) => dot.addEventListener('click', () => showSlide(Number(dot.dataset.slideIndex), true)));

    autoplayButton?.addEventListener('click', () => {
        userPaused = !userPaused;
        updateAutoplayButton();
        startRotation();
    });

    reviewCarousel.addEventListener('mouseenter', () => {
        interactionPaused = true;
        stopRotation();
    });
    reviewCarousel.addEventListener('mouseleave', () => {
        interactionPaused = false;
        startRotation();
    });
    reviewCarousel.addEventListener('focusin', () => {
        interactionPaused = true;
        stopRotation();
    });
    reviewCarousel.addEventListener('focusout', (event) => {
        if (reviewCarousel.contains(event.relatedTarget)) return;
        interactionPaused = false;
        startRotation();
    });
    document.addEventListener('visibilitychange', startRotation);

    updateAutoplayButton();
    showSlide(0);
}

const wizard = document.querySelector('[data-booking-wizard]');
if (wizard) {
    const steps = [...wizard.querySelectorAll('[data-step]')];
    const progressItems = [...document.querySelectorAll('[data-progress-step]')];
    const availabilityRegion = wizard.querySelector('[data-availability-region]');
    const availabilityMessage = wizard.querySelector('[data-availability-message]');
    let current = Number(wizard.dataset.initialStep || 1);
    let availabilityRequest;

    const fieldsForStep = (step) => [...step.querySelectorAll('input, select, textarea')].filter((field) => field.type !== 'hidden');
    const displayStep = (number, shouldScroll = true) => {
        current = number;
        steps.forEach((step) => step.classList.toggle('is-active', Number(step.dataset.step) === number));
        progressItems.forEach((item) => {
            const itemNumber = Number(item.dataset.progressStep);
            item.classList.toggle('is-active', itemNumber === number);
            item.classList.toggle('is-complete', itemNumber < number);
            if (itemNumber === number) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        if (number === 2 && shouldScroll) refreshAvailability();
        if (shouldScroll) document.querySelector('[data-booking-top]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const validateStep = () => {
        const step = steps.find((item) => Number(item.dataset.step) === current);
        if (!step) return true;
        for (const field of fieldsForStep(step)) {
            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    };

    wizard.querySelectorAll('[data-next]').forEach((button) => button.addEventListener('click', () => {
        if (validateStep()) displayStep(Math.min(4, current + 1));
    }));
    wizard.querySelectorAll('[data-back]').forEach((button) => button.addEventListener('click', () => displayStep(Math.max(1, current - 1))));

    // Enter advances the current step, never submits an incomplete wizard.
    wizard.noValidate = true;
    wizard.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!validateStep()) return;
        if (current < 4) return displayStep(current + 1);
        const invalidStep = steps.find((step) => fieldsForStep(step).some((field) => !field.checkValidity()));
        if (invalidStep) {
            displayStep(Number(invalidStep.dataset.step));
            return validateStep();
        }
        const button = wizard.querySelector('[data-confirm-submit]');
        button.disabled = true;
        button.innerHTML = '<i class="ph ph-circle-notch spin"></i> Confirming appointment…';
        wizard.submit();
    });

    const updateSummary = () => {
        const unitType = wizard.querySelector('input[name="aircon_unit_type_id"]:checked');
        const quantity = Number(wizard.querySelector('[name="quantity"]')?.value || 1);
        const price = Number(unitType?.dataset.price || 0);
        const unit = unitType?.dataset.name || 'Not selected';
        const dateValue = wizard.querySelector('[name="appointment_date"]')?.value;
        const timeValue = wizard.querySelector('[name="appointment_time"]:checked')?.value;
        const total = price * quantity;
        document.querySelectorAll('[data-summary-quantity]').forEach((el) => el.textContent = `${quantity} ${quantity === 1 ? 'unit' : 'units'} · ${unit}`);
        document.querySelectorAll('[data-summary-price]').forEach((el) => el.textContent = total ? money.format(total / 100) : '—');
        document.querySelectorAll('[data-summary-schedule]').forEach((el) => {
            if (!dateValue || !timeValue) return el.textContent = 'Select an appointment schedule';
            const date = new Date(`${dateValue}T${timeValue}:00`);
            el.textContent = date.toLocaleString('en-PH', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
        });
    };

    const applyAvailability = (slots) => {
        slots.forEach((slot) => {
            const option = wizard.querySelector(`[data-time-option][data-time="${slot.time}"]`);
            if (!option) return;
            const input = option.querySelector('input');
            const status = option.querySelector('[data-slot-status]');
            const icon = option.querySelector('.time-status-icon i');
            option.classList.remove('is-available', 'is-reserved', 'is-in_progress');
            option.classList.add(`is-${slot.status}`);
            input.disabled = slot.status !== 'available';
            if (input.disabled) input.checked = false;
            status.textContent = slot.status_label;
            icon.className = slot.status === 'available' ? 'ph ph-check' : (slot.status === 'in_progress' ? 'ph ph-clock-countdown' : 'ph ph-prohibit');
        });

        if (!wizard.querySelector('input[name="appointment_time"]:checked')) {
            const firstAvailable = wizard.querySelector('input[name="appointment_time"]:not(:disabled)');
            if (firstAvailable) firstAvailable.checked = true;
        }
        updateSummary();
    };

    const refreshAvailability = async () => {
        const date = wizard.querySelector('[name="appointment_date"]')?.value;
        const quantity = wizard.querySelector('[name="quantity"]')?.value || 1;
        if (!date || !wizard.dataset.availabilityUrl || !availabilityRegion) return;

        availabilityRequest?.abort();
        availabilityRequest = new AbortController();
        availabilityRegion.setAttribute('aria-busy', 'true');
        availabilityRegion.classList.add('is-loading');
        if (availabilityMessage) availabilityMessage.textContent = 'Checking the latest schedule…';

        try {
            const url = new URL(wizard.dataset.availabilityUrl, window.location.origin);
            url.searchParams.set('date', date);
            url.searchParams.set('quantity', quantity);
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: availabilityRequest.signal });
            if (!response.ok) throw new Error('Availability could not be loaded.');
            const payload = await response.json();
            applyAvailability(payload.slots || []);
            if (availabilityMessage) availabilityMessage.textContent = 'Live availability updated just now.';
        } catch (error) {
            if (error.name !== 'AbortError' && availabilityMessage) {
                availabilityMessage.textContent = 'We could not refresh the schedule. Your selection will still be checked before confirmation.';
            }
        } finally {
            availabilityRegion.setAttribute('aria-busy', 'false');
            availabilityRegion.classList.remove('is-loading');
        }
    };

    wizard.querySelectorAll('input, select').forEach((field) => field.addEventListener('change', updateSummary));
    wizard.querySelector('[name="appointment_date"]')?.addEventListener('change', refreshAvailability);
    wizard.querySelector('[name="quantity"]')?.addEventListener('change', refreshAvailability);
    updateSummary();
    displayStep(current, false);
}

const subscriptionForm = document.querySelector('[data-subscription-form]');
if (subscriptionForm) {
    const updateSubscriptionEstimate = () => {
        const unitType = subscriptionForm.querySelector('[name="aircon_unit_type_id"] option:checked');
        const quantity = Number(subscriptionForm.querySelector('[name="quantity"]')?.value || 1);
        const plan = subscriptionForm.querySelector('[name="plan"]:checked');
        const price = Number(unitType?.dataset.price || 0);
        const discount = Number(plan?.dataset.discount || 1);
        const total = Math.round(price * quantity * discount);
        const output = subscriptionForm.querySelector('[data-subscription-price]');
        if (output) output.textContent = total ? money.format(total / 100) : '—';
        subscriptionForm.querySelector('[data-plan-name]').textContent = plan?.dataset.label || 'Choose a plan';
        subscriptionForm.querySelector('[data-plan-frequency]').textContent = plan?.dataset.frequency || '';
        subscriptionForm.querySelector('[data-plan-units]').textContent = `${quantity} ${quantity === 1 ? 'unit' : 'units'} · ${unitType?.dataset.name || ''}`;
    };

    subscriptionForm.querySelectorAll('[name="aircon_unit_type_id"], [name="quantity"], [name="plan"]').forEach((field) => {
        field.addEventListener('change', updateSubscriptionEstimate);
    });
    updateSubscriptionEstimate();
}
