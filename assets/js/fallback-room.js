/**
 * Amak Interior - Procedural 3D Architectural Room Fallback
 * Builds a high-end luxury room using Three.js primitives with procedural textures and PBR materials
 */

import * as THREE from 'three';

/**
 * Creates procedural canvas textures for wood floor, marble, and canvas art.
 */
function createWoodTexture() {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');

    // Base warm oak tone
    ctx.fillStyle = '#6E5136';
    ctx.fillRect(0, 0, 512, 512);

    // Plank lines
    ctx.strokeStyle = '#4A3420';
    ctx.lineWidth = 3;
    const plankHeight = 64;
    for (let y = 0; y < 512; y += plankHeight) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(512, y);
        ctx.stroke();

        // Staggered vertical plank joints
        const offset = (y / plankHeight) % 2 === 0 ? 0 : 128;
        for (let x = offset; x < 512; x += 256) {
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.lineTo(x, y + plankHeight);
            ctx.stroke();
        }
    }

    // Wood grain noise
    for (let i = 0; i < 4000; i++) {
        ctx.fillStyle = Math.random() > 0.5 ? 'rgba(0,0,0,0.04)' : 'rgba(255,255,255,0.03)';
        ctx.fillRect(Math.random() * 512, Math.random() * 512, Math.random() * 40 + 10, 1.5);
    }

    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(4, 4);
    return texture;
}

function createAbstractArtTexture() {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 768;
    const ctx = canvas.getContext('2d');

    // Background textured plaster
    ctx.fillStyle = '#E8E3DA';
    ctx.fillRect(0, 0, 512, 768);

    // Abstract charcoal & brass composition
    ctx.fillStyle = '#1C1C1C';
    ctx.beginPath();
    ctx.arc(220, 320, 160, 0, Math.PI * 2);
    ctx.fill();

    ctx.fillStyle = '#B08D57';
    ctx.beginPath();
    ctx.rect(180, 240, 200, 320);
    ctx.fill();

    ctx.strokeStyle = '#FAF8F5';
    ctx.lineWidth = 4;
    ctx.beginPath();
    ctx.moveTo(80, 600);
    ctx.lineTo(440, 180);
    ctx.stroke();

    const texture = new THREE.CanvasTexture(canvas);
    return texture;
}

/**
 * Builds the procedural luxury interior room.
 * @param {THREE.Scene} scene
 * @returns {Object} References to configurable meshes
 */
export function buildProceduralRoom(scene) {
    const roomGroup = new THREE.Group();
    roomGroup.name = 'ProceduralRoom';

    // 1. FLOOR
    const floorTexture = createWoodTexture();
    const floorMaterial = new THREE.MeshStandardMaterial({
        map: floorTexture,
        roughness: 0.35,
        metalness: 0.05,
    });
    const floorGeo = new THREE.PlaneGeometry(16, 16);
    const floor = new THREE.Mesh(floorGeo, floorMaterial);
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = 0;
    floor.receiveShadow = true;
    floor.name = 'ConfigurableFloor';
    roomGroup.add(floor);

    // 2. WALLS
    const wallMaterial = new THREE.MeshStandardMaterial({
        color: 0xF5F1EA, // Warm Ivory default
        roughness: 0.9,
        metalness: 0.0,
    });

    // Back Wall
    const backWall = new THREE.Mesh(new THREE.PlaneGeometry(16, 8), wallMaterial.clone());
    backWall.position.set(0, 4, -8);
    backWall.receiveShadow = true;
    backWall.name = 'ConfigurableBackWall';
    roomGroup.add(backWall);

    // Left Wall
    const leftWall = new THREE.Mesh(new THREE.PlaneGeometry(16, 8), wallMaterial.clone());
    leftWall.rotation.y = Math.PI / 2;
    leftWall.position.set(-8, 4, 0);
    leftWall.receiveShadow = true;
    leftWall.name = 'ConfigurableLeftWall';
    roomGroup.add(leftWall);

    // Right Wall (with architectural window cutout effect)
    const rightWall = new THREE.Mesh(new THREE.PlaneGeometry(16, 8), wallMaterial.clone());
    rightWall.rotation.y = -Math.PI / 2;
    rightWall.position.set(8, 4, 0);
    rightWall.receiveShadow = true;
    rightWall.name = 'ConfigurableRightWall';
    roomGroup.add(rightWall);

    // Baseboards (Brass Trim)
    const baseboardMat = new THREE.MeshStandardMaterial({ color: 0xB08D57, metalness: 0.85, roughness: 0.25 });
    const baseboardBack = new THREE.Mesh(new THREE.BoxGeometry(16, 0.2, 0.05), baseboardMat);
    baseboardBack.position.set(0, 0.1, -7.95);
    roomGroup.add(baseboardBack);

    // 3. ARCHITECTURAL WINDOW & SKYLINE SPILL (Right Side)
    const windowFrameMat = new THREE.MeshStandardMaterial({ color: 0x1C1C1C, metalness: 0.7, roughness: 0.3 });
    const windowFrame = new THREE.Mesh(new THREE.BoxGeometry(0.1, 6.2, 8.2), windowFrameMat);
    windowFrame.position.set(7.9, 4, -1);
    roomGroup.add(windowFrame);

    const windowGlassMat = new THREE.MeshBasicMaterial({ color: 0xEBF4FA, transparent: true, opacity: 0.85 });
    const windowGlass = new THREE.Mesh(new THREE.PlaneGeometry(8, 6), windowGlassMat);
    windowGlass.rotation.y = -Math.PI / 2;
    windowGlass.position.set(7.85, 4, -1);
    roomGroup.add(windowGlass);

    // 4. LUXURY BOUCLÉ / VELVET SOFA
    const sofaGroup = new THREE.Group();
    sofaGroup.name = 'ConfigurableSofa';
    sofaGroup.position.set(0, 0, -3.5);

    const sofaMaterial = new THREE.MeshStandardMaterial({
        color: 0x222C3A, // Midnight Blue default
        roughness: 0.85,
        metalness: 0.05,
    });

    // Base Frame
    const sofaBase = new THREE.Mesh(new THREE.BoxGeometry(5.2, 0.4, 2.2), sofaMaterial);
    sofaBase.position.y = 0.3;
    sofaBase.castShadow = true;
    sofaBase.receiveShadow = true;
    sofaGroup.add(sofaBase);

    // Seat Cushions (3 segments)
    for (let i = -1; i <= 1; i++) {
        const cushion = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.35, 1.8), sofaMaterial);
        cushion.position.set(i * 1.68, 0.65, 0.1);
        cushion.castShadow = true;
        cushion.receiveShadow = true;
        sofaGroup.add(cushion);
    }

    // Backrest
    const sofaBack = new THREE.Mesh(new THREE.BoxGeometry(5.2, 1.4, 0.5), sofaMaterial);
    sofaBack.position.set(0, 1.2, -0.85);
    sofaBack.castShadow = true;
    sofaBack.receiveShadow = true;
    sofaGroup.add(sofaBack);

    // Armrests
    const leftArm = new THREE.Mesh(new THREE.BoxGeometry(0.45, 1.0, 2.2), sofaMaterial);
    leftArm.position.set(-2.4, 0.8, 0);
    leftArm.castShadow = true;
    leftArm.receiveShadow = true;
    sofaGroup.add(leftArm);

    const rightArm = new THREE.Mesh(new THREE.BoxGeometry(0.45, 1.0, 2.2), sofaMaterial);
    rightArm.position.set(2.4, 0.8, 0);
    rightArm.castShadow = true;
    rightArm.receiveShadow = true;
    sofaGroup.add(rightArm);

    // Sofa Brass Feet
    const legGeo = new THREE.CylinderGeometry(0.04, 0.02, 0.3, 16);
    const legMat = new THREE.MeshStandardMaterial({ color: 0xB08D57, metalness: 0.9, roughness: 0.2 });
    const legPositions = [
        [-2.4, 0.15, 0.9], [2.4, 0.15, 0.9],
        [-2.4, 0.15, -0.9], [2.4, 0.15, -0.9]
    ];
    legPositions.forEach(pos => {
        const leg = new THREE.Mesh(legGeo, legMat);
        leg.position.set(pos[0], pos[1], pos[2]);
        leg.castShadow = true;
        sofaGroup.add(leg);
    });

    roomGroup.add(sofaGroup);

    // 5. WOOL TEXTURED CIRCULAR RUG
    const rugMat = new THREE.MeshStandardMaterial({
        color: 0xEDE8DF,
        roughness: 0.95,
        metalness: 0.0,
    });
    const rug = new THREE.Mesh(new THREE.CircleGeometry(3.2, 48), rugMat);
    rug.rotation.x = -Math.PI / 2;
    rug.position.set(0, 0.01, -1.8);
    rug.receiveShadow = true;
    roomGroup.add(rug);

    // 6. SCULPTURAL BRASS & TRAVERTINE COFFEE TABLE
    const tableGroup = new THREE.Group();
    tableGroup.position.set(0, 0, -1.6);

    // Brass Plinth Base
    const tableBase = new THREE.Mesh(
        new THREE.CylinderGeometry(0.6, 0.8, 0.35, 32),
        new THREE.MeshStandardMaterial({ color: 0xB08D57, metalness: 0.9, roughness: 0.25 })
    );
    tableBase.position.y = 0.18;
    tableBase.castShadow = true;
    tableGroup.add(tableBase);

    // Travertine Marble Top Slab
    const tableTop = new THREE.Mesh(
        new THREE.BoxGeometry(2.4, 0.08, 1.4),
        new THREE.MeshStandardMaterial({ color: 0xDCD5CA, metalness: 0.05, roughness: 0.3 })
    );
    tableTop.position.y = 0.39;
    tableTop.castShadow = true;
    tableTop.receiveShadow = true;
    tableGroup.add(tableTop);

    // Decorative Ceramic Vessel on Table
    const vase = new THREE.Mesh(
        new THREE.CylinderGeometry(0.08, 0.15, 0.4, 16),
        new THREE.MeshStandardMaterial({ color: 0x1C1C1C, roughness: 0.6 })
    );
    vase.position.set(-0.5, 0.6, 0.1);
    vase.castShadow = true;
    tableGroup.add(vase);

    roomGroup.add(tableGroup);

    // 7. ARCHED BRASS FLOOR LAMP
    const lampGroup = new THREE.Group();
    lampGroup.position.set(3.4, 0, -4.2);

    const lampBase = new THREE.Mesh(
        new THREE.CylinderGeometry(0.4, 0.4, 0.08, 32),
        new THREE.MeshStandardMaterial({ color: 0x1C1C1C, metalness: 0.8, roughness: 0.3 })
    );
    lampBase.position.y = 0.04;
    lampGroup.add(lampBase);

    const lampStem = new THREE.Mesh(
        new THREE.CylinderGeometry(0.03, 0.03, 4.2, 16),
        new THREE.MeshStandardMaterial({ color: 0xB08D57, metalness: 0.9, roughness: 0.2 })
    );
    lampStem.position.y = 2.1;
    lampGroup.add(lampStem);

    // Glowing Opal Sphere Shade
    const lampShade = new THREE.Mesh(
        new THREE.SphereGeometry(0.35, 32, 32),
        new THREE.MeshStandardMaterial({
            color: 0xFFF5E6,
            emissive: 0xFFD8A8,
            emissiveIntensity: 0.9,
            roughness: 0.1
        })
    );
    lampShade.position.set(-0.6, 4.2, 0.6);
    lampGroup.add(lampShade);

    // Warm Light Emitted from the Lamp
    const lampLight = new THREE.PointLight(0xFFE4B5, 1.8, 8, 1.5);
    lampLight.position.set(-0.6, 4.2, 0.6);
    lampLight.castShadow = true;
    lampLight.shadow.bias = -0.002;
    lampGroup.add(lampLight);

    roomGroup.add(lampGroup);

    // 8. LARGE FRAMED CONTEMPORARY ARTWORK (Back Wall)
    const artFrameMat = new THREE.MeshStandardMaterial({ color: 0xB08D57, metalness: 0.9, roughness: 0.2 });
    const artFrame = new THREE.Mesh(new THREE.BoxGeometry(3.6, 5.2, 0.08), artFrameMat);
    artFrame.position.set(-2.8, 4.2, -7.92);
    roomGroup.add(artFrame);

    const artCanvasMat = new THREE.MeshStandardMaterial({ map: createAbstractArtTexture(), roughness: 0.7 });
    const artCanvas = new THREE.Mesh(new THREE.PlaneGeometry(3.4, 5.0), artCanvasMat);
    artCanvas.position.set(-2.8, 4.2, -7.86);
    roomGroup.add(artCanvas);

    // Add everything to scene
    scene.add(roomGroup);

    // Return reference bundle for configurator
    return {
        roomGroup,
        walls: [backWall, leftWall, rightWall],
        floor: floor,
        sofa: sofaGroup,
        sofaMaterial: sofaMaterial,
        wallMaterials: [backWall.material, leftWall.material, rightWall.material]
    };
}
