# evias/nem-php Change log

This project follows [Semantic Versioning](CONTRIBUTING.md).

---

### v1.0.0

- revamp of the implemented SDK
- implement necessary model abstraction layer
- implement necessary infrastructure service abstraction layer (NIS Web Services)
- implement NIS API requests
- implement unit test suite for NEM\API and NEM\SDK
- include examples into README.md as a first (will include examples/ folder later)
- implement Keccak+ed25519-ref10(donna) KeyPair generation for NEM KeyPair
- implement some pre-configured MosaicDefinition from the NEM Mainnet Network
- implement Fee Structure into SDK (minimum, messages, mosaics and transaction fees)
- implement NIS Serializer features
- implement Keccak-512 Hasher features
- fix timewindow object creations, adapt for UTC core changes

- implement AES encryption for NEM blockchain encrypted messages
- implement laravel abstraction (Facades and Events)

### v0.0.3

- add IoC binding "nem" for API instantiation with config/nem.php
- add laravel/lumen service provider and configuration specific tests
- add examples to README.md

### v0.0.2

- add packages contracts
- add guzzle request handler
- implement API wrapper class
- add test cases

### v0.0.1

- add contributions notes, add LICENSE and readme file.
- prepare travis.yml for travis CI integration.
