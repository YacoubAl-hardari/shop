import { createRoot } from "react-dom/client";
import App from "./App.tsx";
import "./index.css";

// Set theme on initial load
if (typeof window !== "undefined") {
	const saved = localStorage.getItem("theme");
	const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
	const theme = saved || (prefersDark ? "dark" : "light");
	document.documentElement.classList.remove("light", "dark");
	document.documentElement.classList.add(theme);
}

createRoot(document.getElementById("root")!).render(<App />);
