<template>
    <div class="model-viewer">
        <div class="model-viewer-canvas" ref="canvas"></div>
        <div class="zoomable-scale-control">
            <button type="button" class="zoomable-scale-button zoomable-scale-button--out" @click="zoomOut">Zoom Out</button>
            <button type="button" class="zoomable-scale-button zoomable-scale-button--in" @click="zoomIn">Zoom In</button>
        </div>
    </div>
</template>
<script>
    import Detector from '../helpers/webgl-detector';

    let THREE = window.THREE = require('three');
    let TWEEN = require('@tweenjs/tween.js');
    require('three/examples/js/loaders/GLTFLoader');
    require('three/examples/js/controls/OrbitControls');

    export default {
        props: {
            file: {
                type: String,
                required: true,
            },
            zoom: {
                type: Number,
                default: 1,
            }
        },
        data() {
            return {
                minZoom: 1,
                maxZoom: 8,
                three: {
                    camera: null,
                    controls: null,
                    scene: null,
                    renderer: null,
                    lighting: null,
                    loader: null,
                }
            }
        },
        methods: {
            createCamera() {
                let camera = new THREE.PerspectiveCamera(50, 400 / 300, 0.1, 2000);
                camera.position.set(-1.8, 0.9, 2.7);

                this.three.camera = camera;
            },
            createControls() {
                let controls = new THREE.OrbitControls(this.three.camera, this.$refs.canvas);
                controls.enablePan = false;
                controls.enableKeys = false;
                controls.enableZoom = false;
                controls.minZoom = this.minZoom;
                controls.maxZoom = this.maxZoom;
                controls.minDistance = this.minZoom;
                controls.maxDistance = this.maxZoom;
                controls.target.set(0, 0, 0);
                controls.update();

                this.three.controls = controls;
            },
            createScene() {
                let scene = new THREE.Scene();
                let lighting = new THREE.HemisphereLight(0xbbbbff, 0x444422);
                lighting.position.set(0, 1, 0);
                scene.add(lighting);
                scene.background = new THREE.Color(0xf7f7f7);

                this.three.scene = scene;
                this.three.lighting = lighting;
            },
            loadModel() {
                let loader = new window.THREE.GLTFLoader();
                loader.load(this.file, (gltf) => {
                    this.three.scene.add(gltf.scene);
                });

                this.three.loader = loader;
            },
            createRenderer() {
                let renderer = new THREE.WebGLRenderer({antialias: true});
                renderer.setPixelRatio(window.devicePixelRatio);
                renderer.setSize(400, 300);
                renderer.gammaOutput = true;

                this.$refs.canvas.appendChild(renderer.domElement);

                this.three.renderer = renderer;
            },
            getBounds() {
                let bounds = {
                    width: 0,
                    height: 0,
                };

                if (this.$refs.canvas) {
                    let rect = this.$refs.canvas.getBoundingClientRect();
                    bounds.width = rect.width;
                    bounds.height = rect.height;
                }

                return bounds;
            },
            onResize() {
                let bounds = this.getBounds();
                this.three.renderer.setSize(bounds.width, bounds.height);
                this.three.camera.aspect = bounds.width / bounds.height;
                this.three.camera.updateProjectionMatrix();
            },
            animate() {
                requestAnimationFrame(this.animate);
                this.onResize();
                this.three.renderer.render(this.three.scene, this.three.camera);
                this.three.controls.update();
                TWEEN.update();
            },
            zoomIn() {
                this.setZoom(this.three.camera.zoom * 2);
            },
            zoomOut() {
                this.setZoom(this.three.camera.zoom / 2);
            },
            setZoom(newZoom) {
                newZoom = Math.max(this.minZoom, newZoom);
                newZoom = Math.min(this.maxZoom, newZoom);

                let self = this;
                let currentZoom = { zoom: this.three.camera.zoom };
                let tween = new TWEEN.Tween(currentZoom)
                    .easing(TWEEN.Easing.Quadratic.In)
                    .to({zoom: newZoom}, 200);
                tween.onUpdate(function() {
                    self.three.camera.zoom = this._object.zoom;
                    self.three.camera.updateProjectionMatrix();
                });
                tween.start();
            }
        },
        mounted() {
            // The detector will show a warning if the current browser does not support WebGL.
            if (!Detector.webgl) {
                Detector.addGetWebGLMessage();
            }

            this.createCamera();
            this.createControls();
            this.createScene();
            this.loadModel();
            this.createRenderer();
            this.animate();

            window.addEventListener('resize', this.onResize, false);
        },
        watch: {
            zoom(newZoom) {
                this.setZoom(newZoom);
            }
        }
    }
</script>
