(function () {
    "use strict";

    var header = document.getElementById("siteHeader");
    var navToggle = document.getElementById("navToggle");
    var siteNav = document.getElementById("siteNav");

    function track(eventName, payload) {
        var detail = Object.assign({ event: eventName }, payload || {});
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(detail);
        window.dispatchEvent(new CustomEvent("dms:track", { detail: detail }));
    }

    var utmKeys = ["utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content"];
    var storedUtm = {};
    var query = new URLSearchParams(window.location.search);

    try {
        storedUtm = JSON.parse(window.sessionStorage.getItem("dms_utm") || "{}");
    } catch (error) {
        storedUtm = {};
    }

    utmKeys.forEach(function (key) {
        var value = query.get(key);

        if (value) {
            storedUtm[key] = value;
        }
    });

    try {
        if (Object.keys(storedUtm).length) {
            window.sessionStorage.setItem("dms_utm", JSON.stringify(storedUtm));
        }
    } catch (error) {
        void error;
    }

    track("landing_view", storedUtm);

    if (header) {
        var updateHeader = function () {
            header.classList.toggle("is-scrolled", window.scrollY > 8);
        };

        updateHeader();
        window.addEventListener("scroll", updateHeader, { passive: true });
    }

    if (header && navToggle && siteNav) {
        var setNav = function (open) {
            header.classList.toggle("nav-open", open);
            navToggle.setAttribute("aria-expanded", open ? "true" : "false");
            navToggle.setAttribute("aria-label", open ? "Tutup menu navigasi" : "Buka menu navigasi");
        };

        navToggle.addEventListener("click", function () {
            setNav(!header.classList.contains("nav-open"));
        });

        siteNav.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                if (window.matchMedia("(max-width: 960px)").matches) {
                    setNav(false);
                }
            });
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                setNav(false);
            }
        });

        document.addEventListener("click", function (event) {
            if (!header.contains(event.target)) {
                setNav(false);
            }
        });

        window.addEventListener("resize", function () {
            if (window.innerWidth > 960) {
                setNav(false);
            }
        });
    }

    var billingButtons = Array.prototype.slice.call(document.querySelectorAll("[data-billing]"));
    var priceElements = Array.prototype.slice.call(document.querySelectorAll("[data-price]"));
    var periodElements = Array.prototype.slice.call(document.querySelectorAll("[data-period]"));
    var noteElements = Array.prototype.slice.call(document.querySelectorAll("[data-plan-note]"));

    function setBilling(mode) {
        billingButtons.forEach(function (button) {
            var active = button.getAttribute("data-billing") === mode;
            button.classList.toggle("is-active", active);
            button.setAttribute("aria-pressed", active ? "true" : "false");
        });

        priceElements.forEach(function (price) {
            var value = price.getAttribute("data-" + mode);
            if (value) {
                price.textContent = value;
            }
        });

        periodElements.forEach(function (period) {
            var value = period.getAttribute("data-" + mode);
            if (value !== null) {
                period.textContent = value;
            }
        });

        noteElements.forEach(function (note) {
            var value = note.getAttribute("data-" + mode);
            if (value !== null) {
                note.textContent = value;
            }
        });
    }

    billingButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            setBilling(button.getAttribute("data-billing"));
        });
    });

    document.querySelectorAll(".faq-item").forEach(function (item) {
        item.addEventListener("toggle", function () {
            if (!item.open) {
                return;
            }

            document.querySelectorAll(".faq-item[open]").forEach(function (other) {
                if (other !== item) {
                    other.open = false;
                }
            });
        });
    });

    var revealElements = Array.prototype.slice.call(document.querySelectorAll(".reveal"));
    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (revealElements.length) {
        if (reduceMotion || !("IntersectionObserver" in window)) {
            revealElements.forEach(function (element) {
                element.classList.add("is-visible");
            });
        } else {
            var observer = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add("is-visible");
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { rootMargin: "0px 0px -40px 0px", threshold: 0.08 }
            );

            revealElements.forEach(function (element) {
                observer.observe(element);
            });
        }
    }

    var roiRevenue = document.getElementById("roiRevenue");
    var roiHours = document.getElementById("roiHours");
    var roiMonthly = document.getElementById("roiMonthly");
    var roiYearly = document.getElementById("roiYearly");
    var compactRupiah = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        notation: "compact",
        compactDisplay: "short",
        maximumFractionDigits: 1,
    });

    function formatRupiah(value) {
        try {
            return compactRupiah.format(value);
        } catch (error) {
            return "Rp " + Math.round(value / 1000000) + " jt";
        }
    }

    function updateRoi() {
        if (!roiRevenue || !roiHours || !roiMonthly || !roiYearly) {
            return;
        }

        var revenue = Math.max(0, parseFloat(roiRevenue.value) || 0) * 1000000;
        var hours = Math.max(0, parseFloat(roiHours.value) || 0);
        var monthlyValue = hours * 4 * 30000 + revenue * 0.003;

        roiMonthly.textContent = formatRupiah(monthlyValue) + " / bulan";
        roiYearly.textContent = formatRupiah(monthlyValue * 12) + " / tahun";
    }

    [roiRevenue, roiHours].forEach(function (input) {
        if (input) {
            input.addEventListener("input", updateRoi);
        }
    });

    updateRoi();

    var leadForm = document.getElementById("leadForm");
    var formStatus = document.getElementById("formStatus");
    var leadWhatsapp = document.getElementById("leadWhatsapp");
    var leadPlan = document.getElementById("leadPlan");

    document.querySelectorAll("[data-plan]").forEach(function (element) {
        element.addEventListener("click", function () {
            if (leadPlan) {
                leadPlan.value = element.getAttribute("data-plan") || "";
            }
        });
    });

    if (leadWhatsapp) {
        leadWhatsapp.addEventListener("input", function () {
            leadWhatsapp.setCustomValidity("");
        });
    }

    if (leadForm) {
        leadForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (leadWhatsapp) {
                var digits = leadWhatsapp.value.replace(/\D/g, "");
                if (digits.length < 9 || digits.length > 15) {
                    leadWhatsapp.setCustomValidity("Masukkan nomor WhatsApp yang valid, misalnya 0812 3456 7890.");
                    leadForm.reportValidity();
                    return;
                }
            }

            if (!leadForm.checkValidity()) {
                leadForm.reportValidity();
                return;
            }

            var formData = new FormData(leadForm);
            var number = (leadForm.getAttribute("data-whatsapp") || "").replace(/\D/g, "");
            var lines = [
                "Halo DMS, saya ingin menjadwalkan demo gratis.",
                "",
                "Nama: " + (formData.get("name") || "-"),
                "Perusahaan: " + (formData.get("company") || "-"),
                "WhatsApp: " + (formData.get("whatsapp") || "-"),
                "Jenis usaha: " + (formData.get("industry") || "-"),
                "Jumlah sales: " + (formData.get("sales") || "-"),
            ];
            var planValue = (formData.get("plan") || "").toString().trim();
            var message = (formData.get("message") || "").toString().trim();
            var utmString = Object.keys(storedUtm)
                .map(function (key) {
                    return key + "=" + storedUtm[key];
                })
                .join(", ");

            if (planValue) {
                lines.push("Paket: " + planValue);
            }

            if (message) {
                lines.push("Kebutuhan: " + message);
            }

            if (utmString) {
                lines.push("Sumber: " + utmString);
            }

            var whatsappUrl = "https://wa.me/" + number + "?text=" + encodeURIComponent(lines.join("\n"));

            window.open(whatsappUrl, "_blank", "noopener");

            if (formStatus) {
                formStatus.innerHTML =
                    'Permintaan demo siap dikirim. Jika WhatsApp tidak terbuka otomatis, <a href="' +
                    whatsappUrl +
                    '" target="_blank" rel="noopener">klik di sini</a>.';
                formStatus.classList.add("is-visible");
            }

            track("lead_submit", {
                industry: formData.get("industry"),
                sales: formData.get("sales"),
                plan: planValue,
            });
        });
    }

    document.querySelectorAll("[data-track]").forEach(function (element) {
        if (element.tagName === "FORM") {
            return;
        }

        element.addEventListener("click", function () {
            track(element.getAttribute("data-track"), {
                label: (element.textContent || "").trim().replace(/\s+/g, " ").slice(0, 80),
            });
        });
    });
})();
