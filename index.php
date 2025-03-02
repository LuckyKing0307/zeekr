<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Car Showcase</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/controls/OrbitControls.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/postprocessing/EffectComposer.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/postprocessing/RenderPass.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/shaders/LuminosityHighPassShader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/postprocessing/UnrealBloomPass.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/shaders/CopyShader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128/examples/js/postprocessing/ShaderPass.js"></script>

</head>
<body>
<select id="colorPicker" style="position: absolute; top: 20px; left: 20px; z-index: 10;">
    <option value="0xF8F8F8">Arctic White</option>
    <option value="0x8B5A2B">Dawn Brown</option>
    <option value="0x4A4A4A">Nightfall Gray</option>
    <option value="0x121212">Polar Night Black</option>
    <option value="0x3B6B4C">Forest Green</option>
    <option value="0x5A7D9A">Stone Blue</option>
    <option value="0xE87722">Cloud Orange</option>
</select>

<script>
    let scene, camera, renderer, carModel, controls, composer, raycaster, mouse;
    let doors = {};
    let doorAnimation = {};
    let capot = {};
    let capotAnimation = {};

    function init() {
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0xffffff);

        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.set(0, 2, 5);

        renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        renderer.outputEncoding = THREE.sRGBEncoding;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.5;
        document.body.appendChild(renderer.domElement);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.1;
        controls.rotateSpeed = 0.5;
        controls.enableZoom = true;
        controls.minPolarAngle = Math.PI / 6; // Минимальный угол (примерно 30 градусов)
        controls.maxPolarAngle = Math.PI - Math.PI / 6; // Максимальный угол (примерно 150 градусов)
        window.addEventListener('resize', onWindowResize);

        window.addEventListener('click', onDocumentClick);

        raycaster = new THREE.Raycaster();
        mouse = new THREE.Vector2();
        const ambientLight = new THREE.AmbientLight(0xe0f7fa, 0.5);
        scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xfff2cc, 1.2);
        directionalLight.position.set(10, 10, 10);
        directionalLight.castShadow = true;
        directionalLight.shadow.bias = -0.005;
        scene.add(directionalLight);
        const directionalLight1 = new THREE.DirectionalLight(0xccddff, 0.7);
        directionalLight1.position.set(-5, 5, -5);
        scene.add(directionalLight1);
        const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight2.position.set(0, 8, -10);
        scene.add(directionalLight2);
        const platformGeometry = new THREE.CylinderGeometry(2, 2, 0.2, 1050);
        const platformMaterial = new THREE.MeshStandardMaterial({color: 0xaaaaaa});
        const platform = new THREE.Mesh(platformGeometry, platformMaterial);
        platform.position.y = -0.1;
        platform.receiveShadow = true;
        scene.add(platform);
        const loader = new THREE.GLTFLoader();
        loader.load('car.glb', function (gltf) {
            carModel = gltf.scene;
            const part1 = carModel.getObjectByName('door1');
            console.log(part1);
            carModel.position.y = 0.1;
            carModel.scale.set(1, 1, 1);
            carModel.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                    if (child.material && (child.name.toLowerCase().includes("obj_7x0049"))) {
                        child.material.transparent = true;
                        child.material.opacity = 0.8;
                        child.material.metalness = 0.5;
                        child.material.roughness = 0.1;
                        child.material.color.setHex(0x000000);
                        child.material.depthWrite = false;
                        child.material.side = THREE.DoubleSide;
                        child.material.envMapIntensity = 2; // Улучшение отражений
                        child.material.refractionRatio = 0.98; // Преломление стекла
                    } else {
                        child.material.metalness = 1;
                        child.material.roughness = 0.5;
                    }
                    if (child.material && (child.name.toLowerCase().includes("obj_7x0062") || child.material.name.toLowerCase().includes("mat_7x0016"))) {
                        child.material.transparent = true;
                        child.material.opacity = 0.4;
                        child.material.metalness = 0.5;
                        child.material.roughness = 0.1;
                        child.material.color.setHex(0x000000);
                        child.material.depthWrite = false;
                        child.material.side = THREE.DoubleSide;
                    } else {
                        child.material.metalness = 1;
                        child.material.roughness = 0.5;
                    }
                }
                if (child.name.toLowerCase().includes("obj_7x0001") || child.name.toLowerCase().includes("obj_7x0443") || child.name.toLowerCase().includes("obj_7x0262") || child.name.toLowerCase().includes("obj_7x0308") || child.name.toLowerCase().includes("obj_7x0223") || child.name.toLowerCase().includes("obj_7x0237")) {
                    doors[child.name] = child;
                    if (child.name.toLowerCase().includes("obj_7x0223") || child.name.toLowerCase().includes("obj_7x0237")) {
                        doorAnimation[child.name] = child.parent.rotation.x
                    } else {
                        doorAnimation[child.name] = child.parent.rotation.z
                    }
                }
            });
            composer = new THREE.EffectComposer(renderer);
            const renderPass = new THREE.RenderPass(scene, camera);
            composer.addPass(renderPass);
            const bloomPass = new THREE.UnrealBloomPass(new THREE.Vector2(window.innerWidth, window.innerHeight), 0.3, 0.3, 0.6);

            composer.addPass(bloomPass);
            scene.add(carModel);
        });

        animate();
    }

    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        for (let doorName in doorAnimation) {
            let door = doors[doorName];
            let anim = doorAnimation[doorName];

            if (Math.abs(anim.targetRotation - anim.currentRotation) > 0.01) {
                anim.currentRotation += (anim.targetRotation - anim.currentRotation) * 0.1;
                door.rotation.y = anim.currentRotation;
            }
        }
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.render(scene, camera);
    }

    function changeColor(color) {
        if (carModel) {
            carModel.traverse((child) => {
                if (child.material) {
                    if (child.material.name.toLowerCase().includes("mat_7x0001")) {
                        child.material.color.setHex(color);
                    }
                }
            });
        }
    }

    function onWindowResize() {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    }

    function onDocumentClick(event) {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(Object.values(doors));
        if (intersects.length > 0) {
            const door = intersects[0].object.parent;
            let zrot = doorAnimation[intersects[0].object.name];
            if (intersects[0].object.name.toLowerCase().includes("obj_7x0223") || intersects[0].object.name.toLowerCase().includes("obj_7x0237")) {
                if (door.rotation.x === zrot) {
                    if (intersects[0].object.name.toLowerCase().includes("obj_7x0237")) {
                        door.rotation.x = Math.PI / 2 + 1;
                    } else {
                        door.rotation.x = Math.PI / 4;
                    }
                } else {
                    door.rotation.x = zrot
                }
            } else {
                if (door.rotation.z === zrot) {
                    door.rotation.z = Math.PI / 4 * -1;
                } else {
                    door.rotation.z = zrot
                }
            }
        }
    }

    document.getElementById('colorPicker').addEventListener('change', function () {
        changeColor(parseInt(this.value));
    });

    init();
</script>
</body>
</html>
