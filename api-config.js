// Set SK_BANK_API_URL to a public API URL when deploying the frontend online.
const currentHost = window.location.hostname || "127.0.0.1";
const API_BASE_URL = window.SK_BANK_API_URL || (
	currentHost.endsWith("github.io")
		? ""
		: "http://" + currentHost + ":5000/api"
);
