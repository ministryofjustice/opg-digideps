class ButtonToggler {
    toggleButton(buttonId) {
        let button = document.getElementById(buttonId);
        button.disabled = (buttonId === 'false') ? 'true' : 'false';
    }

    static addToggleEventListener(elementId, buttonId) {
        elementId.addEventListener('onclick', this.toggleButton(buttonId))
    }
}

export default ButtonToggler
