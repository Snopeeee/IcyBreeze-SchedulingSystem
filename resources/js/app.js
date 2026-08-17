import './bootstrap';
import '@phosphor-icons/web/regular';
import '@phosphor-icons/web/fill';
import '@fontsource/poppins/400.css';
import '@fontsource/poppins/500.css';
import '@fontsource/poppins/600.css';
import '@fontsource/poppins/700.css';
import '@fontsource/league-spartan/600.css';
import '@fontsource/league-spartan/700.css';

const money = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 });

document.querySelector('[data-menu-toggle]')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const menu = document.querySelector('[data-site-menu]');
    const open = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!open));
    menu?.classList.toggle('is-open', !open);
    button.querySelector('i')?.classList.toggle('ph-list', open);
    button.querySelector('i')?.classList.toggle('ph-x', !open);
});

document.querySelectorAll('[data-faq-button]').forEach((button) => {
    button.addEventListener('click', () => {
        const item = button.closest('[data-faq-item]');
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        item?.classList.toggle('is-open', !expanded);
    });
});

const wizard = document.querySelector('[data-booking-wizard]');
if (wizard) {
    const steps = [...wizard.querySelectorAll('[data-step]')];
    const progressItems = [...document.querySelectorAll('[data-progress-step]')];
    let current = Number(wizard.dataset.initialStep || 1);

    const fieldsForStep = (step) => [...step.querySelectorAll('input, select, textarea')].filter((field) => field.type !== 'hidden');
    const displayStep = (number) => {
        current = number;
        steps.forEach((step) => step.classList.toggle('is-active', Number(step.dataset.step) === number));
        progressItems.forEach((item) => {
            const itemNumber = Number(item.dataset.progressStep);
            item.classList.toggle('is-active', itemNumber === number);
            item.classList.toggle('is-complete', itemNumber < number);
        });
        document.querySelector('[data-booking-top]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
            if (!dateValue || !timeValue) return el.textContent = 'Choose your schedule';
            const date = new Date(`${dateValue}T${timeValue}:00`);
            el.textContent = date.toLocaleString('en-PH', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
        });
    };

    wizard.querySelectorAll('input, select').forEach((field) => field.addEventListener('change', updateSummary));
    updateSummary();
    displayStep(current);
}

document.querySelector('[data-confirm-submit]')?.addEventListener('click', (event) => {
    const form = event.currentTarget.closest('form');
    if (form?.checkValidity()) {
        event.currentTarget.disabled = true;
        event.currentTarget.innerHTML = '<i class="ph ph-circle-notch spin"></i> Creating booking…';
        form.submit();
    } else {
        form?.reportValidity();
    }
});

document.querySelectorAll('[data-geolocate]').forEach((button) => {
    button.addEventListener('click', () => {
        const container = button.closest('[data-location-fields]');
        const status = container?.querySelector('[data-location-status]');

        if (!container || !navigator.geolocation) {
            if (status) status.textContent = 'Location is not available in this browser.';
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i class="ph ph-circle-notch spin"></i> Finding location';
        if (status) status.textContent = 'Waiting for your location permission...';

        navigator.geolocation.getCurrentPosition((position) => {
            container.querySelector('[data-latitude]').value = position.coords.latitude.toFixed(7);
            container.querySelector('[data-longitude]').value = position.coords.longitude.toFixed(7);
            const accuracyField = container.querySelector('[data-accuracy]');
            if (accuracyField) accuracyField.value = Math.round(position.coords.accuracy);
            container.querySelector('[data-location-consent]').value = '1';
            if (status) status.textContent = `Location ready (accurate to about ${Math.round(position.coords.accuracy)} m).`;
            button.disabled = false;
            button.innerHTML = '<i class="ph ph-check-circle"></i> Location added';
        }, (error) => {
            const messages = {
                1: 'Location permission was not granted. You can still enter the address.',
                2: 'Your location could not be found. Please try again or use the address.',
                3: 'Location request timed out. Please try again.',
            };
            if (status) status.textContent = messages[error.code] || 'Location could not be added.';
            button.disabled = false;
            button.innerHTML = '<i class="ph ph-map-pin"></i> Try location again';
        }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 });
    });
});

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
    };

    subscriptionForm.querySelectorAll('[name="aircon_unit_type_id"], [name="quantity"], [name="plan"]').forEach((field) => {
        field.addEventListener('change', updateSubscriptionEstimate);
    });
    updateSubscriptionEstimate();
}

const technicianTools = document.querySelector('[data-tech-tools]');
if (technicianTools) {
    const shareButton = technicianTools.querySelector('[data-tech-location]');
    const routeButton = technicianTools.querySelector('[data-route-optimize]');
    const status = technicianTools.querySelector('[data-tech-location-status]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let watchId = null;
    let lastSentAt = 0;

    const saveTechnicianLocation = async (position) => {
        const now = Date.now();
        if (now - lastSentAt < 30000) return;
        lastSentAt = now;

        try {
            const response = await fetch(technicianTools.dataset.locationUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy_meters: position.coords.accuracy,
                }),
            });

            if (!response.ok) throw new Error('Location update failed.');
            if (status) status.textContent = `Live location updated just now (about ${Math.round(position.coords.accuracy)} m accuracy).`;
        } catch {
            if (status) status.textContent = 'We could not update the office. Check your connection and try again.';
        }
    };

    shareButton?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            if (status) status.textContent = 'Location is not available in this browser.';
            return;
        }

        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
            shareButton.innerHTML = '<i class="ph ph-crosshair"></i> Share live location';
            if (status) status.textContent = 'Location sharing is off.';
            return;
        }

        if (status) status.textContent = 'Waiting for location permission...';
        watchId = navigator.geolocation.watchPosition(saveTechnicianLocation, () => {
            if (status) status.textContent = 'Location permission is needed to share field position.';
            if (watchId !== null) navigator.geolocation.clearWatch(watchId);
            watchId = null;
            shareButton.innerHTML = '<i class="ph ph-crosshair"></i> Share live location';
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 15000 });
        shareButton.innerHTML = '<i class="ph ph-stop-circle"></i> Stop sharing';
    });

    routeButton?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            if (status) status.textContent = 'Location is not available in this browser.';
            return;
        }

        routeButton.disabled = true;
        routeButton.innerHTML = '<i class="ph ph-circle-notch spin"></i> Optimizing';
        navigator.geolocation.getCurrentPosition((position) => {
            const url = new URL(technicianTools.dataset.dashboardUrl, window.location.origin);
            url.searchParams.set('lat', position.coords.latitude);
            url.searchParams.set('lng', position.coords.longitude);
            window.location.assign(url);
        }, () => {
            if (status) status.textContent = 'We could not read your current position. Try sharing your location first.';
            routeButton.disabled = false;
            routeButton.innerHTML = '<i class="ph ph-path"></i> Optimize from here';
        }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 });
    });
}
