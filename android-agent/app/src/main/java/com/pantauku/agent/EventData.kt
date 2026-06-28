package com.pantauku.agent

data class EventData(
    val type: String,
    val value: String,
    val deviceId: String,
    val deviceName: String = "",
    val latitude: Double? = null,
    val longitude: Double? = null,
    val occurredAt: String = java.text.SimpleDateFormat(
        "yyyy-MM-dd'T'HH:mm:ss'Z'",
        java.util.Locale.US
    ).format(java.util.Date())
)
