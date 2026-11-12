import {
    AmbientLight,
    BufferGeometry,
    Clock,
    Color,
    DirectionalLight,
    ExtrudeGeometry,
    Float32BufferAttribute,
    Fog,
    Group,
    Mesh,
    MeshStandardMaterial,
    PerspectiveCamera,
    Points,
    PointsMaterial,
    Scene,
    Shape,
    SphereGeometry,
    SRGBColorSpace,
    WebGLRenderer,
} from 'three';

const maxPixelRatio = 2;
const particleCount = 110;

function shieldShape() {
    const shape = new Shape();

    shape.moveTo(-0.72, 1.02);
    shape.lineTo(0.72, 1.02);
    shape.lineTo(1.02, 0.42);
    shape.lineTo(0.84, -0.28);
    shape.lineTo(0.36, -0.82);
    shape.lineTo(0, -1.32);
    shape.lineTo(-0.36, -0.82);
    shape.lineTo(-0.84, -0.28);
    shape.lineTo(-1.02, 0.42);
    shape.closePath();

    return shape;
}

function createShield() {
    const geometry = new ExtrudeGeometry(shieldShape(), {
        depth: 0.18,
        bevelEnabled: true,
        bevelThickness: 0.045,
        bevelSize: 0.04,
        bevelSegments: 1,
        curveSegments: 1,
        steps: 1,
    });
    geometry.center();
    geometry.computeBoundingBox();

    const material = new MeshStandardMaterial({
        color: 0x1aa89b,
        emissive: 0x0e7c74,
        emissiveIntensity: 0.18,
        roughness: 0.62,
        metalness: 0.06,
        flatShading: true,
    });

    return new Mesh(geometry, material);
}

function createPad(radius, widthSegments, heightSegments) {
    return new SphereGeometry(radius, widthSegments, heightSegments);
}

function createPaw(frontZ) {
    const material = new MeshStandardMaterial({
        color: 0xf3faf8,
        roughness: 0.48,
        metalness: 0,
        flatShading: true,
    });
    const group = new Group();
    const pad = new Mesh(createPad(0.24, 7, 5), material);
    pad.scale.set(1.05, 0.82, 0.42);
    pad.position.set(0, -0.08, frontZ);

    const toes = [
        [-0.3, 0.26, 0.1],
        [-0.11, 0.4, 0.095],
        [0.11, 0.4, 0.095],
        [0.3, 0.26, 0.1],
    ];

    toes.forEach(([x, y, radius]) => {
        const toe = new Mesh(createPad(radius, 6, 4), material);
        toe.scale.set(1, 0.78, 0.4);
        toe.position.set(x, y, frontZ);
        group.add(toe);
    });

    group.add(pad);

    return { group, material };
}

function createField() {
    const positions = new Float32Array(particleCount * 3);
    const colors = new Float32Array(particleCount * 3);
    const phases = new Float32Array(particleCount);
    const baseY = new Float32Array(particleCount);
    const teal = new Color(0x19a89b);
    const bright = new Color(0x7dded4);

    for (let index = 0; index < particleCount; index += 1) {
        const offset = index * 3;
        positions[offset] = (Math.random() - 0.35) * 7.2;
        positions[offset + 1] = (Math.random() - 0.5) * 4.4;
        positions[offset + 2] = (Math.random() - 0.6) * 2.4;
        phases[index] = Math.random() * Math.PI * 2;
        baseY[index] = positions[offset + 1];

        const color = index % 3 === 0 ? bright : teal;
        colors[offset] = color.r;
        colors[offset + 1] = color.g;
        colors[offset + 2] = color.b;
    }

    const geometry = new BufferGeometry();
    geometry.setAttribute('position', new Float32BufferAttribute(positions, 3));
    geometry.setAttribute('color', new Float32BufferAttribute(colors, 3));

    const material = new PointsMaterial({
        size: 0.035,
        transparent: true,
        opacity: 0.55,
        depthWrite: false,
        vertexColors: true,
        sizeAttenuation: true,
    });

    return {
        points: new Points(geometry, material),
        baseY,
        phases,
    };
}

export function mountHeroScene(hero) {
    const canvas = hero.querySelector('[data-hero-canvas]');

    if (!canvas || canvas.dataset.mounted === 'true') {
        return;
    }

    canvas.dataset.mounted = 'true';

    let renderer;

    try {
        renderer = new WebGLRenderer({
            canvas,
            antialias: true,
            alpha: false,
            powerPreference: 'high-performance',
        });

        if (!renderer.getContext()) {
            throw new Error('WebGL unavailable');
        }
    } catch {
        delete canvas.dataset.mounted;
        return;
    }

    renderer.outputColorSpace = SRGBColorSpace;
    renderer.setClearColor(0x07181c, 1);

    const scene = new Scene();
    scene.fog = new Fog(0x07181c, 5.2, 9.5);

    const camera = new PerspectiveCamera(32, 1, 0.1, 20);
    camera.position.set(0, 0.08, 5.5);
    camera.lookAt(0.55, 0, 0);

    scene.add(new AmbientLight(0xb7e4de, 0.55));

    const key = new DirectionalLight(0xffffff, 1.35);
    key.position.set(2.4, 2.8, 4);
    scene.add(key);

    const fill = new DirectionalLight(0x0e7c74, 0.45);
    fill.position.set(-3, -1.2, 2);
    scene.add(fill);

    const shield = createShield();
    const front = shield.geometry.boundingBox.max.z + 0.02;
    const paw = createPaw(front);
    const field = createField();
    const emblem = new Group();

    emblem.scale.setScalar(0.42);
    emblem.position.set(1.58, 0.74, 0);
    emblem.add(shield);
    emblem.add(paw.group);
    scene.add(emblem);
    scene.add(field.points);

    const clock = new Clock();
    const pointer = { x: 0, y: 0 };
    let onScreen = true;
    let tabVisible = document.visibilityState === 'visible';
    let wideEnough = true;
    let raf = 0;
    let stopped = false;

    const visual = hero.querySelector('[data-hero-visual]');

    const resize = () => {
        const width = hero.clientWidth;
        const height = hero.clientHeight;
        wideEnough = width > 767;

        if (!wideEnough || width < 2 || height < 2) {
            hero.removeAttribute('data-hero-ready');
            kick();
            return;
        }

        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, maxPixelRatio));
        renderer.setSize(width, height, false);
        hero.dataset.heroReady = 'true';

        if (visual) {
            visual.getAnimations().forEach((animation) => animation.cancel());
            visual.style.opacity = '';
            visual.style.transform = '';
        }

        kick();
    };

    const render = () => {
        raf = 0;

        if (stopped || !onScreen || !tabVisible || !wideEnough) {
            return;
        }

        const time = clock.getElapsedTime();
        const positions = field.points.geometry.attributes.position.array;

        for (let index = 0; index < particleCount; index += 1) {
            positions[index * 3 + 1] = field.baseY[index] + Math.sin(time * 0.28 + field.phases[index]) * 0.07;
        }

        field.points.geometry.attributes.position.needsUpdate = true;
        field.points.rotation.y = time * 0.02;

        const targetY = Math.sin(time * 0.32) * 0.08 + pointer.x * 0.14;
        const targetX = Math.sin(time * 0.21) * 0.04 + pointer.y * 0.08;
        emblem.rotation.y += (targetY - emblem.rotation.y) * 0.06;
        emblem.rotation.x += (targetX - emblem.rotation.x) * 0.06;
        emblem.position.y = 0.74 + Math.sin(time * 0.45) * 0.05;

        renderer.render(scene, camera);
        raf = requestAnimationFrame(render);
    };

    const kick = () => {
        if (!stopped && onScreen && tabVisible && wideEnough && raf === 0) {
            raf = requestAnimationFrame(render);
        }
    };

    const onPointerMove = (event) => {
        const rect = hero.getBoundingClientRect();
        pointer.x = (event.clientX - rect.left) / rect.width - 0.5;
        pointer.y = (event.clientY - rect.top) / rect.height - 0.5;
    };

    const onPointerLeave = () => {
        pointer.x = 0;
        pointer.y = 0;
    };

    const onVisibility = () => {
        tabVisible = document.visibilityState === 'visible';
        kick();
    };

    const observer = new IntersectionObserver(([entry]) => {
        onScreen = entry.isIntersecting;
        kick();
    });

    const onContextLost = (event) => {
        event.preventDefault();
        dispose();
    };

    function dispose() {
        if (stopped) {
            return;
        }

        stopped = true;

        if (raf !== 0) {
            cancelAnimationFrame(raf);
        }

        observer.disconnect();
        hero.removeEventListener('pointermove', onPointerMove);
        hero.removeEventListener('pointerleave', onPointerLeave);
        document.removeEventListener('visibilitychange', onVisibility);
        canvas.removeEventListener('webglcontextlost', onContextLost);
        window.removeEventListener('resize', resize);
        hero.removeAttribute('data-hero-ready');
        delete canvas.dataset.mounted;

        shield.geometry.dispose();
        shield.material.dispose();
        paw.group.children.forEach((child) => child.geometry.dispose());
        paw.material.dispose();
        field.points.geometry.dispose();
        field.points.material.dispose();
        renderer.dispose();
    }

    hero.addEventListener('pointermove', onPointerMove, { passive: true });
    hero.addEventListener('pointerleave', onPointerLeave);
    document.addEventListener('visibilitychange', onVisibility);
    canvas.addEventListener('webglcontextlost', onContextLost);
    window.addEventListener('resize', resize);
    observer.observe(hero);
    resize();
}
