/**
 * Amak Interior - Core 3D Hero Scene
 * WebGL Engine, GLTF Loader, Procedural Fallback, ScrollTrigger Camera & Parallax
 */

import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
import { buildProceduralRoom } from './fallback-room.js';
import { InteriorConfigurator } from './configurator.js';

class SpatialHeroEngine {
    constructor() {
        this.container = document.getElementById('hero-canvas-container');
        this.canvas = document.getElementById('webgl-canvas');
        if (!this.container || !this.canvas) return;

        // Check WebGL Capability
        if (!this.isWebGLAvailable()) {
            this.handleWebGLFallback();
            return;
        }

        // Performance & Device Detect
        this.isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent) || window.innerWidth < 768;
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.roomReferences = null;
        this.configurator = null;
        
        // Mouse Parallax Targets
        this.mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };
        this.isRendering = true;
        this.clock = new THREE.Clock();

        this.init();
    }

    isWebGLAvailable() {
        try {
            const canvas = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
        } catch (e) {
            return false;
        }
    }

    handleWebGLFallback() {
        const fallback = document.getElementById('hero-fallback');
        const loader = document.getElementById('site-loader');
        if (fallback) fallback.classList.remove('hidden');
        if (this.canvas) this.canvas.style.display = 'none';
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        }
    }

    init() {
        // 1. Scene Setup
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x1C1C1C);
        this.scene.fog = new THREE.FogExp2(0x1C1C1C, 0.035);

        // 2. Camera Setup
        const aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(42, aspect, 0.1, 100);
        this.camera.position.set(0, 2.3, 5.5);
        this.camera.lookAt(0, 1.4, -2.0);

        // 3. Renderer Setup
        this.renderer = new THREE.WebGLRenderer({
            canvas: this.canvas,
            antialias: !this.isMobile,
            alpha: false,
            powerPreference: 'high-performance',
            stencil: false,
            depth: true
        });

        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, this.isMobile ? 1.5 : 2.0));
        this.renderer.shadowMap.enabled = !this.isMobile;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.15;
        this.renderer.outputColorSpace = THREE.SRGBColorSpace;

        // 4. Lighting System
        this.setupLighting();

        // 5. Load Asset or Build Fallback
        this.loadEnvironment();

        // 6. Bind Event Listeners
        this.bindEvents();

        // 7. Start Render Loop
        this.animate();
    }

    setupLighting() {
        // Ambient fill
        const ambientLight = new THREE.AmbientLight(0xFFF8EE, 0.65);
        this.scene.add(ambientLight);

        // Main Sunlight / Key Light through window
        const sunLight = new THREE.DirectionalLight(0xFFF2DC, 2.2);
        sunLight.position.set(9, 8, 4);
        sunLight.castShadow = !this.isMobile;
        sunLight.shadow.mapSize.width = 2048;
        sunLight.shadow.mapSize.height = 2048;
        sunLight.shadow.camera.near = 0.5;
        sunLight.shadow.camera.far = 25;
        sunLight.shadow.camera.left = -6;
        sunLight.shadow.camera.right = 6;
        sunLight.shadow.camera.top = 6;
        sunLight.shadow.camera.bottom = -6;
        sunLight.shadow.bias = -0.0005;
        this.scene.add(sunLight);

        // Warm Fill Light
        const fillLight = new THREE.DirectionalLight(0xD4B27C, 0.7);
        fillLight.position.set(-6, 4, 2);
        this.scene.add(fillLight);

        // Subtle Ceiling Downlight Accent
        const ceilingPoint = new THREE.PointLight(0xFFE8D0, 0.8, 12, 1);
        ceilingPoint.position.set(0, 5, 0);
        this.scene.add(ceilingPoint);
    }

    loadEnvironment() {
        let environmentLoaded = false;
        const progressBar = document.getElementById('loader-progress-bar');
        const statusText = document.getElementById('loader-status-text');

        const fallbackTimer = setTimeout(() => {
            if (!environmentLoaded) {
                environmentLoaded = true;
                console.info('Amak Interior: Generating procedural architectural room fallback.');
                if (!this.roomReferences) {
                    this.roomReferences = buildProceduralRoom(this.scene);
                    this.onRoomReady();
                }
                this.hideLoader();
            }
        }, 700);

        const loadingManager = new THREE.LoadingManager();

        loadingManager.onProgress = (url, itemsLoaded, itemsTotal) => {
            const progress = (itemsLoaded / itemsTotal) * 100;
            if (progressBar) progressBar.style.width = `${progress}%`;
            if (statusText) statusText.textContent = `Assembling Materials (${Math.round(progress)}%)...`;
        };

        loadingManager.onLoad = () => {
            if (!environmentLoaded) {
                environmentLoaded = true;
                clearTimeout(fallbackTimer);
                this.hideLoader();
            }
        };

        loadingManager.onError = () => {
            if (!environmentLoaded) {
                environmentLoaded = true;
                clearTimeout(fallbackTimer);
                if (!this.roomReferences) {
                    this.roomReferences = buildProceduralRoom(this.scene);
                    this.onRoomReady();
                }
                this.hideLoader();
            }
        };

        const gltfLoader = new GLTFLoader(loadingManager);
        const dracoLoader = new DRACOLoader();
        dracoLoader.setDecoderPath('https://unpkg.com/three@0.164.1/examples/jsm/libs/draco/');
        gltfLoader.setDRACOLoader(dracoLoader);

        const modelPath = this.container.getAttribute('data-model-path') || 'assets/models/room.glb';

        // Try to load user .glb or fallback to procedural room
        gltfLoader.load(
            modelPath,
            (gltf) => {
                if (!environmentLoaded) {
                    environmentLoaded = true;
                    clearTimeout(fallbackTimer);
                    const model = gltf.scene;
                    model.traverse((child) => {
                        if (child.isMesh) {
                            child.castShadow = !this.isMobile;
                            child.receiveShadow = !this.isMobile;
                        }
                    });
                    this.scene.add(model);
                    this.roomReferences = { scene: model };
                    this.onRoomReady();
                    this.hideLoader();
                }
            },
            undefined,
            (error) => {
                if (!environmentLoaded) {
                    environmentLoaded = true;
                    clearTimeout(fallbackTimer);
                    console.info('Amak Interior: Custom .glb not found, generating procedural architectural room.');
                    this.roomReferences = buildProceduralRoom(this.scene);
                    this.onRoomReady();
                    this.hideLoader();
                }
            }
        );
    }

    onRoomReady() {
        // Initialize Material Configurator
        this.configurator = new InteriorConfigurator(this.roomReferences);

        // Initialize ScrollTrigger Camera Sequences
        if (!this.prefersReducedMotion && window.gsap && window.ScrollTrigger) {
            this.initScrollAnimations();
        }
    }

    initScrollAnimations() {
        // Synchronize 3D camera fly-through on scroll
        gsap.to(this.camera.position, {
            x: 1.2,
            y: 1.8,
            z: 2.2,
            ease: 'none',
            scrollTrigger: {
                trigger: '#hero-canvas-container',
                start: 'top top',
                end: '+=250%',
                scrub: 1.0
            }
        });

        gsap.to(this.camera.rotation, {
            y: -0.18,
            x: 0.05,
            ease: 'none',
            scrollTrigger: {
                trigger: '#hero-canvas-container',
                start: 'top top',
                end: '+=250%',
                scrub: 1.0
            }
        });
    }

    hideLoader() {
        const loader = document.getElementById('site-loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 700);
        }

        // Animate initial headline entrance
        if (window.gsap) {
            gsap.from('#hero-headline-wrap .animate-hero', {
                y: 40,
                opacity: 0,
                duration: 1.2,
                stagger: 0.18,
                ease: 'power3.out',
                delay: 0.3
            });
        }
    }

    bindEvents() {
        // Mouse Move Parallax
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth) * 2 - 1;
            const y = -(e.clientY / window.innerHeight) * 2 + 1;
            this.mouse.targetX = x * 0.35;
            this.mouse.targetY = y * 0.2;
        });

        // Device Orientation for Mobile Parallax
        if (window.DeviceOrientationEvent && this.isMobile) {
            window.addEventListener('deviceorientation', (e) => {
                if (e.gamma !== null && e.beta !== null) {
                    this.mouse.targetX = (e.gamma / 45) * 0.25;
                    this.mouse.targetY = ((e.beta - 45) / 45) * 0.15;
                }
            });
        }

        // Resize with Debounce
        window.addEventListener('resize', () => {
            if (!this.container || !this.camera || !this.renderer) return;
            const width = this.container.clientWidth;
            const height = this.container.clientHeight;

            this.camera.aspect = width / height;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(width, height);
            this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, this.isMobile ? 1.5 : 2.0));
        });

        // Tab Visibility & Intersection Observer Optimization
        document.addEventListener('visibilitychange', () => {
            this.isRendering = !document.hidden;
        });

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    this.isRendering = entry.isIntersecting && !document.hidden;
                });
            }, { threshold: 0.05 });
            observer.observe(this.container);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            this.dispose();
        });
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        if (!this.isRendering || !this.renderer || !this.scene || !this.camera) return;

        // Smooth Mouse Parallax Interpolation (Lerp)
        if (!this.prefersReducedMotion) {
            this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.05;
            this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.05;

            this.camera.position.x += (this.mouse.x * 0.6 - (this.camera.position.x - 0)) * 0.02;
            this.camera.position.y += ((2.3 + this.mouse.y * 0.4) - this.camera.position.y) * 0.02;
        }

        this.renderer.render(this.scene, this.camera);
    }

    dispose() {
        this.isRendering = false;
        if (this.renderer) {
            this.renderer.dispose();
        }
        if (this.scene) {
            this.scene.traverse((obj) => {
                if (obj.geometry) obj.geometry.dispose();
                if (obj.material) {
                    if (Array.isArray(obj.material)) {
                        obj.material.forEach((m) => m.dispose());
                    } else {
                        obj.material.dispose();
                    }
                }
            });
        }
    }
}

// Auto-initialize when DOM is ready or immediately if document is already interactive
function initSpatialEngine() {
    if (!window.amakHeroEngine) {
        window.amakHeroEngine = new SpatialHeroEngine();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSpatialEngine);
} else {
    initSpatialEngine();
}
