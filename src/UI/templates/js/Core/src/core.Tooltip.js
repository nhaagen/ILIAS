class Tooltip {
    constructor(element) {
        this.container = element.parentElement;
        this.element = element;
        this.document = element.ownerDocument;
        this.window = this.document.defaultView || this.document.parentWindow;

        var tooltip_id = this.element.getAttribute("aria-describedby");
        if (tooltip_id == null) {
            throw new Error("Could not find expected attribute aria-describedby for element with tooltip.");
        }

        this.tooltip = this.document.getElementById(tooltip_id);
        if (this.tooltip == null) {
            throw new Error("Tooltip " + foo + " not found.", {cause: this.element});
        }

        this.showTooltip = this.showTooltip.bind(this);
        this.hideTooltip = this.hideTooltip.bind(this);
        this.onKeyDown = this.onKeyDown.bind(this);
        this.onPointerDown = this.onPointerDown.bind(this);

        this.bindElementEvents();
        this.bindContainerEvents();
    }

    showTooltip() {
        this.container.classList.add("c-tooltip--visible");
        this.bindDocumentEvents();

        this.checkVerticalBounds();
    }

    hideTooltip() {
        this.container.classList.remove("c-tooltip--visible");
        this.unbindDocumentEvents();

        this.container.classList.remove("c-tooltip--top");
    }

    bindElementEvents() {
        this.element.addEventListener("focus", this.showTooltip);
        this.element.addEventListener("blur", this.hideTooltip);
    }

    bindContainerEvents() {
        this.container.addEventListener("mouseenter", this.showTooltip);
        this.container.addEventListener("touchstart", this.showTooltip);
        this.container.addEventListener("mouseleave", this.hideTooltip);
    }

    bindDocumentEvents() {
        this.document.addEventListener("keydown", this.onKeyDown)
        this.document.addEventListener("pointerdown", this.onPointerDown)
    }

    unbindDocumentEvents() {
        this.document.removeEventListener("keydown", this.onKeyDown)
        this.document.removeEventListener("pointerdown", this.onPointerDown)
    }

    onKeyDown(event) {
        if (event.key === "Esc" || event.key === "Escape") {
            this.hideTooltip();
        }
    }

    onPointerDown(event) {
        if(event.target === this.element || event.target === this.tooltip) {
            event.preventDefault();
        }
        else {
            this.hideTooltip();
            this.element.blur();
        }
    }

    checkVerticalBounds() {
        var ttRect = this.tooltip.getBoundingClientRect();

        if (ttRect.bottom > this.window.innerHeight) {
            this.container.classList.add("c-tooltip--top");
        }
    }
}

export default Tooltip;
