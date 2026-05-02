package com.issue.tracker.users;

import java.util.List;
import java.util.Optional;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;

@Controller
@RequestMapping
public class UserController{

	@Autowired
	private UsersRepository service;
	
	@Autowired
    BCryptPasswordEncoder bCryptPasswordEncoder;
	
	@GetMapping("/user/list")
	public String list(Model model) {
		List<Users> users = service.findAll();
		model.addAttribute("usersList", users);
		return "user_index";
	}

	// --------------------------------------
	
	@GetMapping("/user/add")
	public String add(Model model) {
		model.addAttribute("usersForm", new Users());
		model.addAttribute("formAction", "save");
		return "user_form"; 
	}
	
	@PostMapping("/user/save")
	public String save(Users i, Model model) {
		String encPass =  bCryptPasswordEncoder.encode(i.getPassword());
		i.setPassword(encPass);
		service.save(i);
		return "redirect:/user/list";
	}
	
	// --------------------------------------

	@GetMapping("/user/edit/{id}")
	public String edit(@PathVariable int id, Model model) {
		Optional<Users> user = service.findById(id); 
		model.addAttribute("usersForm",user);
		model.addAttribute("formAction", "update");
		return "user_form";
	}
	
	@PostMapping("/user/update")
	public String update(Users i, Model model) {
		
		System.out.println("Boop>" + i.getUserId());
		Users updateUser = service.findById(i.getUserId()).orElse(null);
		
		if (!i.getPassword().isEmpty()) {
			String encPass =  bCryptPasswordEncoder.encode(i.getPassword());
			updateUser.setPassword(encPass);
		}
				
		updateUser.setUsername(i.getUsername());
		service.save(updateUser);
		return "redirect:/user/list";
	}
	
	// --------------------------------------
	
	@GetMapping("/user/delete/{id}")
	public String delete(@PathVariable int id) {
		service.deleteById(id);
		return "redirect:/user/list";
	}
	
	// --------------------------------------
	
	@GetMapping("/login")
	public String login(Model model) {
		model.addAttribute("usersForm",new Users());
		return "login"; 
	}
	@GetMapping("/login-error")
	public String login_error(Model model) {
		model.addAttribute("usersForm",new Users());
		model.addAttribute("loginError",true);
		return "login"; 
	}
	
	@GetMapping("/register")
	public String register(Model model) {
		model.addAttribute("usersForm", new Users());
		model.addAttribute("formAction", "register");
		return "user_form"; 
	}
	
	@PostMapping("/user/register")
	public String register_save(Users i, Model model) {
		String encPass =  bCryptPasswordEncoder.encode(i.getPassword());
		i.setPassword(encPass);
		service.save(i);
		return "redirect:/";
	}
}
