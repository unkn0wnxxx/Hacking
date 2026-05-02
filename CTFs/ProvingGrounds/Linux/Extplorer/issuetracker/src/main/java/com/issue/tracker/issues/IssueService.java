package com.issue.tracker.issues;

import java.util.List;
import java.util.Optional;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class IssueService implements IssueInterface {

	@Autowired
	private IssueRepository data;
	
	@Override
	public List<Issue> GetAll() {
		return (List<Issue>)data.findAll();
	}

	@Override
	public Optional<Issue> GetId(int id) {
		return data.findById(id);
	}

	@Override
	public int Save(Issue i) {
		int res=0;
		Issue issue = data.save(i);
		if (!issue.equals(null)) {
			res=1;
		}
		
		return res;
	}

	@Override
	public void Delete(int id) {
		data.deleteById(id);
	}

}
