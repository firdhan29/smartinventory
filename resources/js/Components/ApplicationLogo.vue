<template>
    <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
        <!-- Definition of Gradients and Filters -->
        <defs>
            <linearGradient id="primaryGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#4f46e5" /> <!-- Indigo 600 -->
                <stop offset="100%" stop-color="#06b6d4" /> <!-- Cyan 500 -->
            </linearGradient>
            <linearGradient id="accentGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#3b82f6" /> <!-- Blue 500 -->
                <stop offset="100%" stop-color="#8b5cf6" /> <!-- Purple 500 -->
            </linearGradient>
            <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="15" result="blur" />
                <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
        </defs>

        <!-- Outer Premium Tech Ring -->
        <circle cx="250" cy="250" r="220" fill="none" stroke="url(#accentGradient)" stroke-width="4" stroke-dasharray="15 8" opacity="0.3" />
        <circle cx="250" cy="250" r="200" fill="none" stroke="url(#primaryGradient)" stroke-width="2" opacity="0.5" />

        <!-- Isometric Box (representing Warehouse/Inventory) -->
        <!-- Front Left Face -->
        <path d="M 250,110 L 120,185 L 120,335 L 250,260 Z" fill="url(#primaryGradient)" opacity="0.95" />
        <!-- Front Right Face -->
        <path d="M 250,110 L 380,185 L 380,335 L 250,260 Z" fill="url(#accentGradient)" opacity="0.85" />
        <!-- Top Face -->
        <path d="M 250,110 L 380,185 L 250,260 L 120,185 Z" fill="#ffffff" opacity="0.15" />

        <!-- Futuristic "S" & "I" stylized cuts on the faces -->
        <!-- Smart Lightning / 'S' Symbol in negative space/overlay -->
        <path d="M 250,150 L 170,195 L 250,240 L 330,195 Z" fill="#ffffff" opacity="0.2" />
        
        <!-- Glowing Inner Tech Core (Representing "Smart" automation) -->
        <circle cx="250" cy="225" r="45" fill="url(#primaryGradient)" filter="url(#glow)" opacity="0.8" />
        <circle cx="250" cy="225" r="25" fill="#ffffff" opacity="0.9" />

        <!-- Dynamic Arrows/Nodes for Mutasi/Inventory Flow -->
        <path d="M 90,170 Q 150,140 210,175" fill="none" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-dasharray="1 15" opacity="0.8" />
        <path d="M 410,170 Q 350,140 290,175" fill="none" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-dasharray="1 15" opacity="0.8" />
    </svg>
</template>
