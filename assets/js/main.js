/**
 * Main JavaScript File
 */

$(document).ready(function () {
  // Auto-hide alerts after 5 seconds
  setTimeout(function () {
    $(".alert").fadeOut("slow");
  }, 5000);

  // Confirm before delete/cancel actions
  $(".confirm-action").on("click", function (e) {
    if (!confirm("Are you sure you want to perform this action?")) {
      e.preventDefault();
      return false;
    }
  });

  // Time slot selection
  $(".time-slot:not(.disabled)").on("click", function () {
    $(".time-slot").removeClass("selected");
    $(this).addClass("selected");
    $("#selected_time").val($(this).data("time"));
  });

  // Form validation enhancement
  $("form[data-validate]").on("submit", function (e) {
    let isValid = true;
    $(this)
      .find("[required]")
      .each(function () {
        if ($(this).val() === "") {
          isValid = false;
          $(this).addClass("is-invalid");
        } else {
          $(this).removeClass("is-invalid");
        }
      });

    if (!isValid) {
      e.preventDefault();
      alert("Please fill in all required fields.");
      return false;
    }
  });

  // Password strength indicator
  $("#password").on("keyup", function () {
    const password = $(this).val();
    let strength = 0;

    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;

    const indicator = $("#password-strength");
    indicator.removeClass();

    switch (strength) {
      case 0:
      case 1:
        indicator.addClass("text-danger").text("Weak");
        break;
      case 2:
      case 3:
        indicator.addClass("text-warning").text("Medium");
        break;
      case 4:
      case 5:
        indicator.addClass("text-success").text("Strong");
        break;
    }
  });

  // Date input - disable past dates
  const today = new Date().toISOString().split("T")[0];
  $('input[type="date"]').attr("min", today);

  // Search functionality
  $("#searchInput").on("keyup", function () {
    const value = $(this).val().toLowerCase();
    $("#searchTable tbody tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

/**
 * Show loading spinner
 */
function showLoading() {
  const spinner = `
        <div class="spinner-wrapper">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
  $("#content-area").html(spinner);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
  return "$" + parseFloat(amount).toFixed(2);
}

/**
 * Format date
 */
function formatDate(dateString) {
  const options = { year: "numeric", month: "long", day: "numeric" };
  return new Date(dateString).toLocaleDateString(undefined, options);
}

/**
 * Format time
 */
function formatTime(timeString) {
  const time = new Date("2000-01-01 " + timeString);
  return time.toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}
