package com.issue.tracker.issues;

import java.util.List;
import java.util.Optional;

public interface IssueInterface {
	
	public List<Issue>GetAll();
	public Optional<Issue>GetId(int id);
	public int Save(Issue i);
	public void Delete(int id);

}
