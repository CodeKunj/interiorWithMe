/**
 * Amak Interior - 360° Panorama & 3D Interactive Model Viewer
 * For project detail pages (equirectangular projection & standalone Orbit viewer)
 */

import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

/**
 * Initializes 360° Equirectangular Panorama Viewer
 */
export function initPanoramaViewer(containerId, imageUrl) {
    const container = document.getElementById(containerId);
    if (!container || !imageUrl) return;

    const width = container.clientWidth;
    const height = container.clientHeight;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(70, width / height, 0.1, 1000);
    camera.position.set(0, 0, 0.1);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    container.innerHTML = '';
    container.appendChild(renderer.domElement);

    // OrbitControls configured for inside-sphere looking out
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableZoom = false;
    controls.enablePan = false;
    controls.rotateSpeed = -0.3;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.5;

    // Load Equirectangular Panorama Texture
    const textureLoader = new THREE.TextureLoader();
    textureLoader.load(imageUrl, (texture) => {
        texture.colorSpace = THREE.SRGBColorSpace;
        const geometry = new THREE.SphereGeometry(500, 60, 40);
        geometry.scale(-1, 1, 1); // Invert normals to view from inside

        const material = new THREE.MeshBasicMaterial({ map: texture });
        const sphere = new THREE.Mesh(geometry, material);
        scene.add(sphere);
    }, undefined, () => {
        // Fallback procedural panorama if image 404
        const geometry = new THREE.SphereGeometry(500, 32, 32);
        geometry.scale(-1, 1, 1);
        const material = new THREE.MeshBasicMaterial({ color: 0x252525, wireframe: true });
        scene.add(new THREE.Mesh(geometry, material));
    });

    let isInteracting = false;
    renderer.domElement.addEventListener('pointerdown', () => { controls.autoRotate = false; isInteracting = true; });
    renderer.domElement.addEventListener('pointerup', () => { isInteracting = false; setTimeout(() => { if (!isInteracting) controls.autoRotate = true; }, 3000); });

    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        const w = container.clientWidth;
        const h = container.clientHeight;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    });
}

/**
 * Initializes Standalone 3D Model Inspector Viewer
 */
export function initModelViewer(containerId, modelUrl) {
    const container = document.getElementById(containerId);
    if (!container || !modelUrl) return;

    const width = container.clientWidth;
    const height = container.clientHeight;

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x181818);

    const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
    camera.position.set(4, 3, 5);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    container.innerHTML = '';
    container.appendChild(renderer.domElement);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.maxPolarAngle = Math.PI / 2 + 0.1;

    // Studio Lighting
    const ambientLight = new THREE.AmbientLight(0xFFFFFF, 0.8);
    scene.add(ambientLight);

    const keyLight = new THREE.DirectionalLight(0xFFF2DC, 1.8);
    keyLight.position.set(5, 8, 5);
    scene.add(keyLight);

    const fillLight = new THREE.DirectionalLight(0xD4B27C, 0.6);
    fillLight.position.set(-5, 3, -5);
    scene.add(fillLight);

    // Floor grid indicator
    const grid = new THREE.GridHelper(10, 20, 0xB08D57, 0x333333);
    grid.position.y = -0.01;
    scene.add(grid);

    // Load Model
    const loader = new GLTFLoader();
    loader.load(modelUrl, (gltf) => {
        const model = gltf.scene;
        // Center model in view
        const box = new THREE.Box3().setFromObject(model);
        const center = box.getCenter(new THREE.Vector3());
        model.position.sub(center);
        scene.add(model);
    });

    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }
    animate();

    window.addEventListener('resize', () => {
        const w = container.clientWidth;
        const h = container.clientHeight;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    });
}
