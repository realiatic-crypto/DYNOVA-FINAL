import React, { useEffect } from "react";

export default function App() {
  useEffect(() => {
    // The PHP app is mounted under /api/ (ingress routes /api/* to backend port 8001).
    window.location.replace("/api/");
  }, []);

  return (
    <div
      data-testid="redirect-page"
      style={{
        minHeight: "100vh",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        background: "#04070f",
        color: "#e6e8ee",
        fontFamily: "Sora, Inter, system-ui, sans-serif",
      }}
    >
      <div style={{ textAlign: "center" }}>
        <div style={{ fontSize: 28, fontWeight: 700, letterSpacing: 6 }}>
          DYNOVA
        </div>
        <div style={{ opacity: 0.6, marginTop: 6, letterSpacing: 4 }}>
          N E T W O R K
        </div>
        <div style={{ marginTop: 24, opacity: 0.7 }}>
          Loading app…&nbsp;
          <a
            href="/api/"
            style={{ color: "#7cc4ff", textDecoration: "underline" }}
          >
            Click here if not redirected
          </a>
        </div>
      </div>
    </div>
  );
}
