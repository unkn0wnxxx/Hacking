package com.issue.tracker.issues;

import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Table;

@Entity
@Table(name = "issue")
public class Issue {
	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	private int Id;
	private String message;
	private String priority;
	
	// Source > Generate Constructor Superclass
	public Issue() {
		super();
		// TODO Auto-generated constructor stub
	}

	// Source > Generate Constructor From Fields
	public Issue(int id, String message, String priority) {
		super();
		Id = id;
		this.message = message;
		this.priority = priority;
	}

	// Source > Generate Getter and Setter
	public int getId() {
		return Id;
	}

	public void setId(int id) {
		Id = id;
	}

	public String getMessage() {
		return message;
	}

	public void setMessage(String message) {
		this.message = message;
	}

	public String getPriority() {
		return priority;
	}

	public void setPriority(String priority) {
		this.priority = priority;
	}
	
	
}
