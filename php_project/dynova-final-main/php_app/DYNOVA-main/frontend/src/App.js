import { useEffect } from "react";

/**
 * The actual application is a PHP MVC project mounted at /api/.
 * The Emergent ingress routes everything under /api to the backend service,
 * which proxies to the PHP server. This React app simply forwards visitors
 * to the PHP front-controller.
 */
function App() {
  useEffect(() => {
    window.location.replace("/api/");
  }, []);
  return (
    <div
      style={{
        minHeight: "100vh",
        display: "grid",
        placeItems: "center",
        background:
          "radial-gradient(ellipse at 30% 10%, rgba(62,182,255,.25), transparent 50%), radial-gradient(ellipse at 80% 90%, rgba(141,91,255,.25), transparent 50%), #04070f",
        color: "#e7ecff",
        fontFamily: "Sora, system-ui, sans-serif",
      }}
    >
      <div style={{ textAlign: "center" }}>
        <div
          style={{
            width: 90,
            height: 90,
            margin: "0 auto 18px",
            borderRadius: 24,
            background:
              "radial-gradient(circle at 30% 30%, #0e1740, #04070f 70%)",
            border: "1px solid rgba(120,170,255,.25)",
            boxShadow:
              "0 0 40px rgba(62,182,255,.4), inset 0 0 30px rgba(141,91,255,.18)",
            display: "grid",
            placeItems: "center",
          }}
        >
          <img
            src="/api/assets/img/logo.jpg"
            alt="Dynova"
            style={{ width: "100%", height: "100%", borderRadius: 24, objectFit: "cover" }}
            onError={(e) => (e.currentTarget.style.display = "none")}
          />
        </div>
        <div
          style={{
            fontSize: 26,
            fontWeight: 800,
            letterSpacing: 6,
            background:
              "linear-gradient(135deg,#3eb6ff 0%,#7a5bff 60%,#ff5be0 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
          }}
        >
          DYNOVA
        </div>
        <div style={{ fontSize: 11, letterSpacing: 8, color: "#3eb6ff" }}>
          N E T W O R K
        </div>
        <p style={{ marginTop: 22, color: "#8a93b8", fontSize: 13 }}>
          Loading your dashboard...
        </p>
      </div>
    </div>
  );
}

export default App;
