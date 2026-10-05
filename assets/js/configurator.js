/**
 * Amak Interior - 3D Material Configurator Module
 * Allows interactive real-time swapping of wall colors, floor finishes, and sofa upholstery
 */

import * as THREE from 'three';

export class InteriorConfigurator {
    /**
     * @param {Object} roomReferences - References from fallback-room or GLTF hierarchy
     */
    constructor(roomReferences) {
        this.room = roomReferences;
        this.activeTab = 'walls';

        // Predefined Design Palettes
        this.wallPalettes = {
            'ivory':    { hex: '#F5F1EA', threeColor: 0xF5F1EA, name: 'Warm Lime-wash Ivory' },
            'sage':     { hex: '#8B9B88', threeColor: 0x8B9B88, name: 'Minimalist Sage' },
            'clay':     { hex: '#C49A82', threeColor: 0xC49A82, name: 'Tuscan Terracotta' },
            'slate':    { hex: '#3B444B', threeColor: 0x3B444B, name: 'Nordic Slate' },
            'charcoal': { hex: '#1C1C1C', threeColor: 0x1C1C1C, name: 'Deep Monolith Charcoal' },
        };

        this.floorPalettes = {
            'oak':      { color: 0x6E5136, roughness: 0.35, name: 'Smoked French Oak' },
            'marble':   { color: 0xE5E0D8, roughness: 0.15, name: 'Calacatta Gold Travertine' },
            'concrete': { color: 0x8C8882, roughness: 0.55, name: 'Polished Micro-Cement' },
        };

        this.sofaPalettes = {
            'midnight': { hex: '#222C3A', threeColor: 0x222C3A, roughness: 0.85, name: 'Midnight Velvet' },
            'boucle':   { hex: '#EDE8DF', threeColor: 0xEDE8DF, roughness: 0.95, name: 'Italian Raw Bouclé' },
            'leather':  { hex: '#6E472A', threeColor: 0x6E472A, roughness: 0.45, name: 'Saddle Cognac Leather' },
            'emerald':  { hex: '#1C3B2B', threeColor: 0x1C3B2B, roughness: 0.80, name: 'Forest Mohair' },
        };

        this.initUI();
    }

    initUI() {
        // Tab switching
        const tabBtns = document.querySelectorAll('.config-tab-btn');
        const tabPanels = document.querySelectorAll('.config-tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-target');
                tabBtns.forEach(b => b.classList.remove('border-brass', 'text-brass'));
                tabBtns.forEach(b => b.classList.add('border-transparent', 'text-stone-subtle'));
                btn.classList.add('border-brass', 'text-brass');
                btn.classList.remove('border-transparent', 'text-stone-subtle');

                tabPanels.forEach(panel => {
                    if (panel.id === `config-tab-${target}`) {
                        panel.classList.remove('hidden');
                    } else {
                        panel.classList.add('hidden');
                    }
                });
            });
        });

        // Wall Color Swatches
        document.querySelectorAll('.wall-swatch').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const colorKey = btn.getAttribute('data-color');
                this.applyWallColor(colorKey);
                this.updateActiveSwatch('.wall-swatch', btn);
            });
        });

        // Floor Finish Swatches
        document.querySelectorAll('.floor-swatch').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const floorKey = btn.getAttribute('data-floor');
                this.applyFloorFinish(floorKey);
                this.updateActiveSwatch('.floor-swatch', btn);
            });
        });

        // Sofa Fabric Swatches
        document.querySelectorAll('.sofa-swatch').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const sofaKey = btn.getAttribute('data-sofa');
                this.applySofaFabric(sofaKey);
                this.updateActiveSwatch('.sofa-swatch', btn);
            });
        });
    }

    updateActiveSwatch(selector, activeEl) {
        document.querySelectorAll(selector).forEach(el => el.classList.remove('active'));
        activeEl.classList.add('active');
    }

    applyWallColor(key) {
        const option = this.wallPalettes[key];
        if (!option || !this.room) return;

        const targetColor = new THREE.Color(option.threeColor);

        // Update procedural room wall materials or GLTF meshes
        if (this.room.wallMaterials) {
            this.room.wallMaterials.forEach(mat => {
                if (window.gsap) {
                    window.gsap.to(mat.color, {
                        r: targetColor.r,
                        g: targetColor.g,
                        b: targetColor.b,
                        duration: 0.8,
                        ease: 'power2.out'
                    });
                } else {
                    mat.color.set(targetColor);
                }
            });
        } else if (this.room.scene) {
            // Search loaded GLB hierarchy for wall meshes
            this.room.scene.traverse(child => {
                if (child.isMesh && (child.name.toLowerCase().includes('wall') || child.material?.name.toLowerCase().includes('wall'))) {
                    if (window.gsap && child.material.color) {
                        window.gsap.to(child.material.color, {
                            r: targetColor.r,
                            g: targetColor.g,
                            b: targetColor.b,
                            duration: 0.8
                        });
                    }
                }
            });
        }

        this.showConfigToast(`Wall: ${option.name}`);
    }

    applyFloorFinish(key) {
        const option = this.floorPalettes[key];
        if (!option || !this.room) return;

        const targetColor = new THREE.Color(option.color);

        if (this.room.floor && this.room.floor.material) {
            const mat = this.room.floor.material;
            if (window.gsap) {
                window.gsap.to(mat.color, {
                    r: targetColor.r,
                    g: targetColor.g,
                    b: targetColor.b,
                    duration: 0.8
                });
                window.gsap.to(mat, {
                    roughness: option.roughness,
                    duration: 0.8
                });
            } else {
                mat.color.set(targetColor);
                mat.roughness = option.roughness;
            }
        }

        this.showConfigToast(`Floor: ${option.name}`);
    }

    applySofaFabric(key) {
        const option = this.sofaPalettes[key];
        if (!option || !this.room) return;

        const targetColor = new THREE.Color(option.threeColor);

        if (this.room.sofaMaterial) {
            const mat = this.room.sofaMaterial;
            if (window.gsap) {
                window.gsap.to(mat.color, {
                    r: targetColor.r,
                    g: targetColor.g,
                    b: targetColor.b,
                    duration: 0.8
                });
                window.gsap.to(mat, {
                    roughness: option.roughness,
                    duration: 0.8
                });
            } else {
                mat.color.set(targetColor);
                mat.roughness = option.roughness;
            }
        }

        this.showConfigToast(`Fabric: ${option.name}`);
    }

    showConfigToast(text) {
        const toast = document.getElementById('config-status-pill');
        if (toast) {
            toast.textContent = text;
            toast.classList.remove('opacity-0');
            toast.classList.add('opacity-100');

            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0');
            }, 2500);
        }
    }
}
