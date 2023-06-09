import { useFlash } from "./useFlash";
import { ref, watch, watchEffect } from "vue";
import { useUser } from "@/utils/useUser";
import { generateToast } from "@/utils/createToast";

export const useFlashAlertEmitter = () => {
    const flash = useFlash();

    const alerts = ref(flash.value.alerts);

    //we compare json here to try and prevent double display of the alert. but it doesn't work.
    watchEffect(() => {
        const newAlert = flash.value.alerts;
        if (JSON.stringify(alerts.value) !== JSON.stringify(newAlert)) {
            alerts.value = newAlert;
        }
    });

    watch(
        alerts,
        (newAlerts) => {
            console.log("new alerts", newAlerts);
            Object.keys(alerts.value).forEach((type) => {
                let messages = new Set(alerts.value[type]);

                console.log({ type, messages });

                messages.forEach(function (text) {
                    let toastText =
                        typeof text === "string" && text.length > 0
                            ? text
                            : "Feature Coming Soon!";
                    generateToast(toastText, { type });
                });
            });
        },
        {
            flush: "sync", // needed so updates are immediate.
        }
    );
};
