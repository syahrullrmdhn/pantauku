package com.pantauku.agent

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.provider.Settings
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class SetupActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_setup)

        val etDeviceName = findViewById<EditText>(R.id.etDeviceName)
        etDeviceName.setText(PrefsManager.getDeviceName(this))
        etDeviceName.setSelection(etDeviceName.text.length)

        findViewById<Button>(R.id.btnAccessibility).setOnClickListener {
            startActivity(Intent(Settings.ACTION_ACCESSIBILITY_SETTINGS))
            Toast.makeText(this, "Cari \"Pan Browser\" lalu aktifkan", Toast.LENGTH_LONG).show()
        }

        findViewById<Button>(R.id.btnBattery).setOnClickListener {
            val intent = Intent(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS).apply {
                data = Uri.parse("package:$packageName")
            }
            try {
                startActivity(intent)
            } catch (e: Exception) {
                startActivity(Intent(Settings.ACTION_BATTERY_SAVER_SETTINGS))
            }
        }

        findViewById<Button>(R.id.btnStart).setOnClickListener {
            // Save device name
            val name = etDeviceName.text.toString().trim()
            if (name.isNotEmpty()) {
                PrefsManager.setDeviceName(this, name)
            }

            val serviceIntent = Intent(this, PantauKuService::class.java)
            if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                startForegroundService(serviceIntent)
            } else {
                startService(serviceIntent)
            }
            EventQueue.init(applicationContext)
            Toast.makeText(this, "Pan Browser siap digunakan", Toast.LENGTH_SHORT).show()
            
            // Launch browser activity
            startActivity(Intent(this, BrowserActivity::class.java))
            finish()
        }

        // Request location permission
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.M) {
            if (checkSelfPermission(android.Manifest.permission.ACCESS_FINE_LOCATION)
                != android.content.pm.PackageManager.PERMISSION_GRANTED) {
                requestPermissions(
                    arrayOf(
                        android.Manifest.permission.ACCESS_FINE_LOCATION,
                        android.Manifest.permission.ACCESS_COARSE_LOCATION
                    ),
                    1001
                )
            }
        }

        val statusText = findViewById<TextView>(R.id.tvStatus)
        statusText.text = "Pan Browser v1.0\nSiap digunakan"
    }

    override fun onRequestPermissionsResult(
        requestCode: Int,
        permissions: Array<out String>,
        grantResults: IntArray
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        if (requestCode == 1001) {
            val allGranted = grantResults.isNotEmpty() &&
                grantResults.all { it == android.content.pm.PackageManager.PERMISSION_GRANTED }
            if (allGranted) {
                Toast.makeText(this, "GPS diizinkan — lokasi akan dikirim ke dashboard", Toast.LENGTH_SHORT).show()
            } else {
                Toast.makeText(this, "GPS tidak diizinkan — maps tidak akan tersedia", Toast.LENGTH_SHORT).show()
            }
        }
    }
}
