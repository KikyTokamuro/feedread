import "./bootstrap";

const initPreviewFeedItems = () => {
    const previewButtons = document.querySelectorAll(".preview-feed-item-btn");
    const modalLabel = document.getElementById("previewFeedItemModalLabel");
    const iframe = document.getElementById("preview-feed-item-iframe");
    const loadingSpinner = document.getElementById("preview-loading-spinner");

    const showSpinner = () => {
        loadingSpinner.classList.remove("d-none");
        loadingSpinner.classList.add("d-flex");
        iframe.style.opacity = "0.5";
    };

    const hideSpinner = () => {
        loadingSpinner.classList.add("d-none");
        loadingSpinner.classList.remove("d-flex");
        iframe.style.opacity = "1";
    };

    iframe.addEventListener("load", function () {
        hideSpinner();
    });

    iframe.addEventListener("error", function () {
        hideSpinner();
    });

    previewButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const btn = this.closest(".preview-feed-item-btn");
            const cardTitle = btn.parentNode.querySelector(".card-title a");
            const iframeLink = btn.getAttribute("data-iframe-link");

            if (cardTitle) {
                const text = cardTitle.textContent.trim();

                modalLabel.textContent = text;
                showSpinner();
                iframe.src = iframeLink;
            }
        });
    });

    const modal = document.getElementById("previewFeedItemModal");
    modal.addEventListener("hidden.bs.modal", function () {
        hideSpinner();
        iframe.src = "";
    });

    modal.addEventListener("show.bs.modal", function () {
        hideSpinner();
    });
};

document.addEventListener("DOMContentLoaded", function () {
    initPreviewFeedItems();
});
