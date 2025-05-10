function validateLoginForm() {
  const e = document.getElementById("username").value,
    t = document.getElementById("password").value
  let n = !0
  return (
    (document.getElementById("username-error").textContent = ""),
    (document.getElementById("password-error").textContent = ""),
    "" === e.trim() && ((document.getElementById("username-error").textContent = "Username is required"), (n = !1)),
    "" === t.trim() && ((document.getElementById("password-error").textContent = "Password is required"), (n = !1)),
    n
  )
}
function validateRegisterForm() {
  const e = document.getElementById("username").value,
    t = document.getElementById("email").value,
    n = document.getElementById("password").value,
    r = document.getElementById("confirm_password").value
  let o = !0
  if (
    ((document.getElementById("username-error").textContent = ""),
    (document.getElementById("email-error").textContent = ""),
    (document.getElementById("password-error").textContent = ""),
    (document.getElementById("confirm-password-error").textContent = ""),
    "" === e.trim()
      ? ((document.getElementById("username-error").textContent = "Username is required"), (o = !1))
      : e.length < 3 &&
        ((document.getElementById("username-error").textContent = "Username must be at least 3 characters"), (o = !1)),
    "" === t.trim()
      ? ((document.getElementById("email-error").textContent = "Email is required"), (o = !1))
      : /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t) ||
        ((document.getElementById("email-error").textContent = "Please enter a valid email address"), (o = !1)),
    "" === n.trim()
      ? ((document.getElementById("password-error").textContent = "Password is required"), (o = !1))
      : n.length < 6 &&
        ((document.getElementById("password-error").textContent = "Password must be at least 6 characters"), (o = !1)),
    "" === r.trim()
      ? ((document.getElementById("confirm-password-error").textContent = "Please confirm your password"), (o = !1))
      : n !== r &&
        ((document.getElementById("confirm-password-error").textContent = "Passwords do not match"), (o = !1)),
    !o)
  ) {
    const e = document.getElementById("register-form"),
      t = document.createElement("div")
    return (
      (t.className = "alert alert-danger mb-3"),
      (t.textContent = "Please fix the errors in the form before submitting."),
      e.parentNode.insertBefore(t, e),
      !1
    )
  }
  return !0
}
function validateTaskForm() {
  const e = document.getElementById("title").value,
    t = document.getElementById("description").value,
    n = document.getElementById("status").value,
    r = document.getElementById("due_date").value
  let o = !0
  if (
    ((document.getElementById("title-error").textContent = ""),
    document.getElementById("due-date-error") && (document.getElementById("due-date-error").textContent = ""),
    document.querySelector(".alert-danger") && document.querySelector(".alert-danger").remove(),
    "" === e.trim()
      ? ((document.getElementById("title-error").textContent = "Title is required"), (o = !1))
      : e.length > 100 &&
        ((document.getElementById("title-error").textContent = "Title must be less than 100 characters"), (o = !1)),
    !n || "" === n.trim())
  ) {
    const e = document.querySelector(".form-select + .invalid-feedback")
    e && (e.textContent = "Please select a status"), (o = !1)
  }
  if (r) {
    const e = new Date()
    e.setHours(0, 0, 0, 0)
    const t = new Date(r)
    t < e &&
      (document.getElementById("due-date-error") &&
        (document.getElementById("due-date-error").textContent = "Due date cannot be in the past"),
      (o = !1))
  }
  if (!o) {
    const e = document.getElementById("task-form"),
      t = document.createElement("div")
    return (
      (t.className = "alert alert-danger mb-3"),
      (t.textContent = "Please fix the errors in the form before submitting."),
      e.parentNode.insertBefore(t, e),
      !1
    )
  }
  return !0
}
document.addEventListener("DOMContentLoaded", () => {
  const e = document.getElementById("login-form")
  e &&
    e.addEventListener("submit", (e) => {
      validateLoginForm() || e.preventDefault()
    })
  const t = document.getElementById("register-form")
  t &&
    t.addEventListener("submit", (e) => {
      validateRegisterForm() || e.preventDefault()
    })
  const n = document.getElementById("task-form")
  n &&
    n.addEventListener("submit", (e) => {
      validateTaskForm() || e.preventDefault()
    })
})
