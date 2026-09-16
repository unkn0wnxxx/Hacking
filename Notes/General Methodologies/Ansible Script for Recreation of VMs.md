
```
---
- name: Setup Kali Linux Environment
  hosts: localhost
  become: true
  vars:
    current_user: lkm
    target_hostname: rfv-004
    git_repos:
      - "https://github.com/topotam/PetitPotam.git"
      - "https://github.com/lgandx/Responder.git"
      - "https://github.com/dirkjanm/krbrelayx.git"
      - "https://github.com/unkn0wnxxx/tooling.git"
      - "https://github.com/unkn0wnxxx/arsenal.git"
  tasks:
    # -------------------------------------------------------------------
    # 1. User Privileges & System Base Configuration
    # -------------------------------------------------------------------
    - name: Ensure user '{{ current_user }}' exists
      ansible.builtin.user:
        name: "{{ current_user }}"
        shell: /bin/zsh
        groups: sudo
        append: yes
        state: present

    - name: Remove sudo password requirement for {{ current_user }}
      ansible.builtin.lineinfile:
        path: /etc/sudoers.d/{{ current_user }}
        line: '{{ current_user }} ALL=(ALL) NOPASSWD:ALL'
        create: yes
        mode: '0440'
        validate: 'visudo -cf %s'

    - name: Set persistent German keyboard layout
      ansible.builtin.command: localectl set-x11-keymap de
      changed_when: false
      ignore_errors: true

    - name: Set Timezone to CET (Europe/Berlin)
      community.general.timezone:
        name: Europe/Berlin

    # -------------------------------------------------------------------
    # 2. Hostname Configuration
    # -------------------------------------------------------------------
    - name: Set system hostname
      ansible.builtin.hostname:
        name: "{{ target_hostname }}"

    - name: Ensure /etc/hosts maps 127.0.0.1 to target hostname
      ansible.builtin.lineinfile:
        path: /etc/hosts
        regexp: '^127\.0\.0\.1\s+'
        line: "127.0.0.1\tlocalhost {{ target_hostname }}"
        state: present
        backup: yes

    # -------------------------------------------------------------------
    # 3. APT Repositories & Package Installations
    # -------------------------------------------------------------------
    - name: Download VS Code GPG Key
      ansible.builtin.get_url:
        url: https://packages.microsoft.com/keys/microsoft.asc
        dest: /etc/apt/trusted.gpg.d/microsoft.asc
        mode: '0644'

    - name: Add VS Code APT Repository
      ansible.builtin.apt_repository:
        repo: "deb [arch=amd64,arm64,armhf signed-by=/etc/apt/trusted.gpg.d/microsoft.asc] https://packages.microsoft.com/repos/code stable main"
        state: present
        filename: vscode

    - name: Update package cache and install APT tools
      ansible.builtin.apt:
        update_cache: yes
        name:
          - keyboard-configuration
          - seclists
          - hashcat
          - certipy-ad
          - golang
          - python3-pip
          - wget
          - gpg
          - apt-transport-https
          - code
        state: present

    # -------------------------------------------------------------------
    # 4. Directory Structure & Tools Setup
    # -------------------------------------------------------------------
    - name: Ensure user directories exist
      ansible.builtin.file:
        path: "/home/{{ current_user }}/{{ item }}"
        state: directory
        owner: "{{ current_user }}"
        group: "{{ current_user }}"
        mode: '0755'
      loop:
        - customers
        - tools

# 1. Schreibrechte auf /opt für deinen User gewähren (damit SSH-Keys greifen)
    - name: Ensure current_user has write permissions to /opt
      ansible.builtin.file:
        path: /opt
        state: directory
        owner: root
        group: "{{ current_user }}"
        mode: '0775'
      become: true

    # 2. Alle Repositories direkt in /opt klonen
    - name: Clone Git security repositories directly into /opt
      ansible.builtin.git:
        repo: "{{ item }}"
        dest: "/opt/{{ item | basename | regex_replace('\\.git$', '') }}"
        clone: yes
        update: yes
      loop: "{{ git_repos }}"
      become: true
      become_user: "{{ current_user }}" # Klonen als User (nutzt deinen SSH-Key)
```

1. Create VM with Kali ISO & Hypervisor 2 Software

2. Open Shell & execute the following command:

