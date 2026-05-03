# CTF Writeup: PassCode

---

Step 1: Viewed Target IP in Web --> Blockchain Challenge with source code
Step 2: Analyzed it --> flag get's unlocked if user says secret code
Step 3: Added Environment Variables, rpcurl, apiurl, contract address, playeraddress & is_solved, stored
given values from target ip endpoint
Step 4: After some research I was stuck and had to lookup --> Read the secret code from contract storage
with --> cast storage $CONTRACT_ADDRESS 2 --rpc-url $RPC_URL
Step 5: Got ouputted an hexadezimal 0x14d which converts into 333 --> secret code = 333
Step 6: made --> cast send $CONTRACT_ADDRESS "unlock(uint256)" 333 --rpc-url $RPC_URL --private-key $PRIVATE_KEY --legacy to call the function with the secret code
Step 7: checked if is_solved function is now true with --> cast call $CONTRACT_ADDRESS "isSolved()(bool) --rpc-url $RPC_URL --> true
Step 8: Got the flag --> cast call $CONTRACT_ADDRESS "getFlag()(string)" --rpc-url $RPC_URL


---

## Key Learnings

- Strengthened Linux Knowledge --> Environment variables, executing of certain scripts & understanding CLI better
- Gained Knowledge about basic blockchains and blockchain explorer for wallet transaction tracking
- Improved Methodology when it comes to solving issues by working with a to-do list.
