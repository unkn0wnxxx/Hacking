package com.issue.tracker.issues;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.List;
import java.util.Optional;
import java.util.Properties;

import javax.persistence.EntityManager;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;

@Controller
@RequestMapping
public class IssueController {

	Connection conn = null;
	
	@Autowired
	private IssueInterface service;
	
	@Autowired
	EntityManager em;
	
	@GetMapping("/")
	public String index(Model model) {
		List<Issue> issues = service.GetAll();
		model.addAttribute("issuesList", issues);
		return "index";
	}
	
	@GetMapping("/issue/list")
	public String list(Model model) {
		List<Issue> issues = service.GetAll();
		model.addAttribute("issuesList", issues);
		return "issue_index";
	}
	
	@GetMapping("/issue/add")
	public String add(Model model) {

		model.addAttribute("issuesForm", new Issue());
		return "issue_form"; 
	}
	@PostMapping("/issue/save")
	public String save(Issue i, Model model) {
		service.Save(i);
		return "redirect:/issue/list";
	}
	
	@GetMapping("/issue/checkByPriority")
	public String checkByPriority(@RequestParam("priority") String priority, Model model) {
		// 
		// Custom code, need to integrate to the JPA
		//
	    Properties connectionProps = new Properties();
	    connectionProps.put("user", "issue_user");
	    connectionProps.put("password", "ManagementInsideOld797");
        try {
			conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/issue_tracker",connectionProps);
		    String query = "SELECT message FROM issue WHERE priority='"+priority+"'";
            System.out.println(query);
		    Statement stmt = conn.createStatement();
		    stmt.executeQuery(query);

        } catch (SQLException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		}
		
        // TODO: Return the list of the issues with the correct priority
		List<Issue> issues = service.GetAll();
		model.addAttribute("issuesList", issues);
		return "issue_index";
        
	}
	
	@GetMapping("/issue/edit/{id}")
	public String edit(@PathVariable int id, Model model) {
		Optional<Issue> issue = service.GetId(id); 
		model.addAttribute("issuesForm",issue);
		return "issue_form";
	}
	
	@GetMapping("/issue/delete/{id}")
	public String delete(@PathVariable int id, Model model) {
		service.Delete(id);
		return "redirect:/issue/list";
	}
}
